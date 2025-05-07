source ./.env
TARGETNAME=lineconnect

# .phpファイルを対象にする
find . -type d \( -name 'node_modules' -o -name 'dist' -o -name 'vendor' -o -name 'document' -o -name 'frontend' \) -prune -o \( -type f \( -name '*.php'\) \) -print > list
xgettext -k"__" -k"_e" -k"_n" -o languages/${TARGETNAME}.pot --files-from=list --from-code=UTF-8 --copyright-holder=SHIP --package-name='LINE Connect' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com --no-location

#初回はmsginit
if [ ! -f languages/${TARGETNAME}-ja.po ]; then
    msginit --locale=ja_JP.UTF-8 --input=languages/${TARGETNAME}.pot --output=languages/${TARGETNAME}-ja.po --no-translator 
fi

#2回目からはmsgmerge
if [ -f languages/${TARGETNAME}-ja.po ]; then
    msgmerge --backup=simple --suffix='.bak' --update --no-fuzzy-matching languages/${TARGETNAME}-ja.po languages/${TARGETNAME}.pot
fi

#.jsファイルを対象にする
find . -type d \( -name 'node_modules' -o -name 'dist' -o -name 'vendor' -o -name 'document' -o -name 'frontend' \) -prune -o \( -type f \( -name '*.js' \) \) -print > list
xgettext -k"__" -k"_e" -k"_n" -o languages/${TARGETNAME}-js.pot --files-from=list --from-code=UTF-8 --copyright-holder=SHIP --package-name='LINE Connect' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com

#初回はmsginit
if [ ! -f languages/${TARGETNAME}-js-ja.po ]; then
    msginit --locale=ja_JP.UTF-8 --input=languages/${TARGETNAME}-js.pot --output=languages/${TARGETNAME}-js-ja.po --no-translator 
fi

#2回目からはmsgmerge
if [ -f languages/${TARGETNAME}-js-ja.po ]; then
    msgmerge --backup=simple --suffix='.bak' --update --no-fuzzy-matching languages/${TARGETNAME}-js-ja.po languages/${TARGETNAME}-js.pot
fi

# delete list
rm -f list