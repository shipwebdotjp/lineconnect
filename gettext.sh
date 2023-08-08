source ./.env
find . -type d \( -name 'node_modules' -o -name 'src' -o -name 'vendor' \) -prune -o \( -type f \( -name '*.php' -or -name '*.js' \) \) -print > list
xgettext -k"__" -k"_e" -k"_n" -o languages/lineconnect.pot --files-from=list --from-code=UTF-8 --copyright-holder=SHIP --package-name='LINE Connect' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com
#初回はmsginit
#msginit --locale=ja_JP.UTF-8 --input=languages/lineconnect.pot --output=languages/lineconnect-ja.po --no-translator 

#2回目からはmsgmerge
msgmerge --backup=simple --suffix='.bak' --update languages/lineconnect-ja.po languages/lineconnect.pot

