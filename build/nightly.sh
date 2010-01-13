#!/bin/sh

# THIS SCRIPT GENERATES A NIGHTLY BUILD FROM HEAD OR BRANCH

if [ $# -lt 1 ]; then
     echo 1>&2 Usage: 1.0 final, or head
     exit 0
fi

# remove any existing exports
rm -rf aspen-framework

# checkout the latest code from trunk
git clone git://github.com/botskonet/aspen-framework.git
cd aspen-framework

# checkout the proper branch
if [ $1 != "head" ]; then
	git checkout --track -b $1$2 origin/$1$2
fi

# get the svn revision number
gitvers=`git describe`

# add in revision to app.default.config.php
sed -e "s/application_build'] = ''/application_build'] = '$gitvers'/g" app.default.config.php > adc-new.php
mv adc-new.php app.default.config.php

# add in revision to bootstrap define
cd system
sed -e "s/define('FRAMEWORK_REV', '')/define('FRAMEWORK_REV', '$gitvers')/g" bootstrap.php > bootstrap-new.php
mv bootstrap-new.php bootstrap.php
cd ..

#remove dirs
rm -rf tests
rm -rf build

# remove all .git directories
rm -rf .git
rm -f .gitignore
rm -f .DS_Store

# make tarball
tar czvf af-temp.tar.gz *
mv af-temp.tar.gz ../aspen-$gitvers.tar.gz

echo "DEVELOPMENT BUILD COMPLETE, VERSION: $1"