source ../.env
# find . -type d \( -name 'node_modules' -o -name 'dist' -o -name 'vendor' \) -prune -o \( -type f \( -name '*.js' \) \) -print > list
# xgettext -k"__" -k"_e" -k"_n" -o languages/line-dm.pot --files-from=list -L JavaScript --from-code=UTF-8 --copyright-holder=SHIP --package-name='LINE Connect' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com
#初回はmsginit
#msginit --locale=ja_JP.UTF-8 --input=languages/line-dm.pot --output=languages/line-dm-ja.po --no-translator 

#2回目からはmsgmerge
msgmerge --backup=simple --suffix='.bak' --update --no-fuzzy-matching languages/line-dm-ja.po languages/line-dm.pot

