source ../../.env
TARGETNAME=bulkmessage

#初回(languages/${TARGETNAME}-ja.poが存在しなければ)はmsginit
if [ ! -f languages/${TARGETNAME}-ja.po ]; then
    msginit --locale=ja_JP.UTF-8 --input=languages/${TARGETNAME}.pot --output=languages/${TARGETNAME}-ja.po --no-translator 
fi

#2回目からはmsgmerge
if [ -f languages/${TARGETNAME}-ja.po ]; then
    msgmerge --backup=simple --suffix='.bak' --update --no-fuzzy-matching languages/${TARGETNAME}-ja.po languages/${TARGETNAME}.pot
fi