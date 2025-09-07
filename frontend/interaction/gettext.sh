source ../../.env
find . -type d \( -name 'node_modules' -o -name 'dist' -o -name 'vendor' \) -prune -o \( -type f \( -name '*.jsx' \) \) -print > list
# xgettext -k"__" -k"_e" -k"_n" -o languages/line-interaction.pot --files-from=list -L JavaScript --from-code=UTF-8 --copyright-holder=SHIP --package-name='LINE Connect' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com
npx react-gettext-parser --config react-gettext-parser.config.js --output ./languages/line-interaction.pot 'src/**/{*.js,*.jsx,*.ts,*.tsx}'
#初回はmsginit
# msginit --locale=ja_JP.UTF-8 --input=languages/line-interaction.pot --output=languages/line-interaction-ja.po --no-translator 

#2回目からはmsgmerge
msgmerge --backup=simple --suffix='.bak' --update --no-fuzzy-matching languages/line-interaction-ja.po languages/line-interaction.pot

