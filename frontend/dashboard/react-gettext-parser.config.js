// react-gettext-parser.config.js
module.exports = {
    // funcArgumentsMap オプションを使用して、
    // 翻訳対象テキストを抽出する関数とその引数のマッピングを定義します。
    funcArgumentsMap: {
        // ここで '__' という名前の関数を指定し、
        // その最初の引数を 'msgid' (翻訳対象の原文) として認識させます。
        // 2番目の引数 ('lineconnect' の部分) は無視されます。
        __: ['msgid'],

        // もしプロジェクト内で他の gettext 関数 (例: gettext, ngettext) も
        // 使用している場合は、それらのデフォルト設定もここに追加するか、
        // デフォルト設定を維持するためにこの funcArgumentsMap 全体を記述せず、
        // '__' の設定のみをデフォルトに追加する形にする必要があります。
        // しかし、提供された例では '__' のみが対象のようなので、上記の設定で十分です。

        /* 参考: デフォルト設定を含める場合
        gettext: ['msgid'],
        dgettext: [null, 'msgid'],
        ngettext: ['msgid', 'msgid_plural'],
        dngettext: [null, 'msgid', 'msgid_plural'],
        pgettext: ['msgctxt', 'msgid'],
        dpgettext: [null, 'msgctxt', 'msgid'],
        npgettext: ['msgctxt', 'msgid', 'msgid_plural'],
        dnpgettext: [null, 'msgid', 'msgid_plural'],
        __: ['msgid'], // 目的の関数を追加
        */
    },
    // 必要に応じて他のオプションを追加できます
    // 例: 抽出した文字列の前後の空白を削除する場合
    // trim: true,
};