const fs = require('fs');
const path = require('path');
const poFile = path.resolve(__dirname, 'languages/rjsf-ja.po');
const jsonFile = path.resolve(__dirname, 'languages/lineconnect-ja-slc_rjsf.json');

function parsePOFile(content) {
    const messages = {};
    let currentMsgid = '';
    let currentMsgstr = '';
    let headers = {};
    let isCollectingMsgid = false;
    let isCollectingMsgstr = false;

    const lines = content.split('\n');

    for (let line of lines) {
        line = line.trim();

        if (line.startsWith('msgid "')) {
            // 新しいメッセージの開始
            if (currentMsgid && currentMsgstr) {
                if (currentMsgid !== '""') {
                    // メッセージを配列形式で保存
                    messages[stripQuotes(currentMsgid)] = [stripQuotes(currentMsgstr)];
                } else {
                    // ヘッダーの処理
                    const headerLines = stripQuotes(currentMsgstr).split('\\n');
                    for (const headerLine of headerLines) {
                        const [key, value] = headerLine.split(': ');
                        if (key && value) {
                            headers[key] = value;
                        }
                    }
                }
            }
            currentMsgid = line.slice(6);
            currentMsgstr = '';
            isCollectingMsgid = true;
            isCollectingMsgstr = false;
        } else if (line.startsWith('msgstr "')) {
            currentMsgstr = line.slice(7);
            isCollectingMsgid = false;
            isCollectingMsgstr = true;
        } else if (line.startsWith('"')) {
            // 複数行の処理
            if (isCollectingMsgid) {
                currentMsgid = currentMsgid.slice(0, -1) + line.slice(1);
            } else if (isCollectingMsgstr) {
                currentMsgstr = currentMsgstr.slice(0, -1) + line.slice(1);
            }
        } else {
            // 空行やコメント行の場合
            isCollectingMsgid = false;
            isCollectingMsgstr = false;
        }
    }

    // 最後のメッセージを追加
    if (currentMsgid && currentMsgstr && currentMsgid !== '""') {
        messages[stripQuotes(currentMsgid)] = [stripQuotes(currentMsgstr)];
    }

    return { messages, headers };
}

// 引用符を取り除き、エスケープされた文字を処理する
function stripQuotes(str) {
    if (!str) return '';

    // 先頭と末尾の引用符を削除
    str = str.replace(/^"|"$/g, '');

    // エスケープされた文字を処理
    str = str.replace(/\\"/g, '"')           // エスケープされた引用符
        .replace(/\\n/g, '\n')          // 改行
        .replace(/\\t/g, '\t')          // タブ
        .replace(/\\r/g, '\r')          // キャリッジリターン
        .replace(/\\(.)/g, '$1');       // その他のエスケープ文字

    return str;
}

function getCurrentDateTime() {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    const localDate = new Date(now.getTime() - (offset * 60 * 1000));
    return localDate.toISOString().replace('T', ' ').slice(0, 19) + '+0900';
}

function convertToWPJED(messages, domain) {
    return {
        'translation-revision-date': getCurrentDateTime(),
        'generator': 'Custom PO to JED Converter',
        'domain': domain,
        'locale_data': {
            [domain]: {
                "": {
                    'domain': domain,
                    'lang': 'ja',
                    'plural-forms': 'nplurals=1; plural=0;'
                },
                ...messages
            }
        }
    };
}

// メイン処理
fs.readFile(poFile, 'utf8', (err, data) => {
    if (err) {
        console.error('Error reading PO file:', err);
        return;
    }

    const { messages, headers } = parsePOFile(data);
    const jedData = convertToWPJED(messages, 'lineconnect');

    fs.writeFile(jsonFile, JSON.stringify(jedData, null, 2), 'utf8', (err) => {
        if (err) {
            console.error('Error writing JSON file:', err);
            return;
        }
        console.log('Successfully converted PO to WordPress JED JSON');
    });
});