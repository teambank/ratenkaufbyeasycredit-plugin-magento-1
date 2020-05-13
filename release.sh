base="$(cd "$(dirname "$0")"; pwd)";

rm -r build/*

mkdir -p build/lib/Netzkollektiv/EasyCreditApi
mkdir -p build/skin/adminhtml/base/default/easycredit

version=$(php -r "echo current(simplexml_load_file('src/app/code/community/Netzkollektiv/EasyCredit/etc/config.xml')->modules)->version;")

cp -r src/* build/
cp -r module-api/EasyCreditApi/* build/lib/Netzkollektiv/EasyCreditApi/
rsync -r --exclude index.html merchant-interface/dist/* build/skin/adminhtml/base/default/easycredit/
(cd build; tar cvf ext.tar *)

php ../MagentoTarToConnect/magento-tar-to-connect.php package-config.php 
(cd src/ && zip -r - *) > dist/m1-easycredit-$version.zip

rm -r dist/build

# German docs
cd docs/sphinx-docs
rm build/latex/*.pdf
make latexpdf
make html
cd $base
cp docs/sphinx-docs/build/latex/m1-easycredit.pdf dist/

# English docs
cd docs/sphinx-docs-en
rm build/latex/*.pdf
make latexpdf
make html
cd $base
cp docs/sphinx-docs-en/build/latex/m1-easycredit-en.pdf dist/
