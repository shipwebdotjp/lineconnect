#mo ファイルの作成
msgfmt -o languages/lineconnect-ja.mo languages/lineconnect-ja.po

#jed jsonファイルの作成
#docker compose run --rm cli 
wp i18n make-json languages/lineconnect-js-ja.po --no-purge

# jsonファイルのリネーム
for file in languages/lineconnect-js-ja-*.json; do
    # ファイル名からハッシュ部分を抽出
    hash=$(echo "$file" | sed -E 's/languages\/lineconnect-js-ja-(.*)\.json/\1/')
    # 新しいファイル名を作成してリネーム
    mv "$file" "languages/lineconnect-ja-${hash}.json"
    echo "リネーム: $file -> languages/lineconnect-ja-${hash}.json"
done

rm -f languages/lineconnect-js.pot
rm -f languages/lineconnect-js-ja.po