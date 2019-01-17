rm -r build/*

mkdir -p build/lib/Netzkollektiv/EasyCreditApi

cp -r src/* build/
cp -r module-api/EasyCreditApi/* build/lib/Netzkollektiv/EasyCreditApi/
(cd build; tar cvf ext.tar *)

php ../MagentoTarToConnect/magento-tar-to-connect.php package-config.php 
(cd src/ && zip -r - *) > dist/file-release.zip

rm -r dist/build

# German docs
cd sphinx-docs
make latexpdf
make html
cd ..
cp sphinx-docs/build/latex/m1-easycredit.pdf dist/

# English docs
cd sphinx-docs-en
make latexpdf
make html
cd ..
cp sphinx-docs-en/build/latex/m1-easycredit-en.pdf dist/
