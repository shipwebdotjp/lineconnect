#mo ファイルの作成
msgfmt -o languages/lineconnect-ja.mo languages/lineconnect-ja.po

#jed jsonファイルの作成
docker compose run --rm cli wp i18n make-json wp-content/plugins/lineconnect/languages/lineconnect-ja.po --no-purge