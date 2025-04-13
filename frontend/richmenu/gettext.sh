source ../../.env
TARGETNAME=richmenu
# find . -type d \( -name 'node_modules' -o -name 'dist' -o -name 'vendor' \) -prune -o \( -type f \( -name '*.js' \) \) -print > list
# xgettext -k"__" -k"_e" -k"_n" -o languages/line-richmenu.pot --files-from=list -L JavaScript --from-code=UTF-8 --copyright-holder=SHIP --package-name='LINE Connect' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com

#初回(languages/${TARGETNAME}-ja.poが存在しなければ)はmsginit
if [ ! -f languages/${TARGETNAME}-ja.po ]; then
    msginit --locale=ja_JP.UTF-8 --input=languages/${TARGETNAME}.pot --output=languages/${TARGETNAME}-ja.po --no-translator 
fi

#2回目からはmsgmerge
if [ -f languages/${TARGETNAME}-ja.po ]; then
    msgmerge --backup=simple --suffix='.bak' --update --no-fuzzy-matching languages/${TARGETNAME}-ja.po languages/${TARGETNAME}.pot
fi

