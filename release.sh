base="$(cd "$(dirname "$0")"; pwd)";

rm -r build/*

mkdir -p build/lib/Netzkollektiv/EasyCreditApi

cp -r src/* build/
cp -r module-api/EasyCreditApi/* build/lib/Netzkollektiv/EasyCreditApi/
(cd build; tar cvf ext.tar *)

php ../MagentoTarToConnect/magento-tar-to-connect.php package-config.php 
(cd src/ && zip -r - *) > dist/file-release.zip

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
