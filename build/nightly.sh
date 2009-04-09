#!/bin/sh

# THIS SCRIPT WAS DESIGNED TO PREPARE ASPEN FRAMEWORK FOR A DEVELOPMENT RELEASE

# remove any existing exports
rm -rf trunk

# checkout the latest code from trunk
svn co http://svn.trellisdevelopment.com/aspen-framework/trunk
cd trunk

# get svn vers
svnvers=`svnversion .`

#remove support dirs
rm -rf docs
rm -rf build
rm -rf tests

# remove all .svn directories
find . -name .svn -exec rm -rf {} \;

# add in revision to app.default.config.php
sed -e "s/application_build'] = ''/application_build'] = '$svnvers'/g" app.default.config.php > adc-new.php
mv adc-new.php app.default.config.php

# add in revision to bootstrap define
cd system
sed -e "s/define('FRAMEWORK_REV', '')/define('FRAMEWORK_REV', '$svnvers')/g" bootstrap.php > bootstrap-new.php
mv bootstrap-new.php bootstrap.php
cd ..

# make tarball
tar czvf af-temp.tar.gz *
mv af-temp.tar.gz ../aspen-trunk-$svnvers.tar.gz
cd ..
rm -rf trunk

# get file size
fsize=$(du -ks aspen-trunk-$svnvers.tar.gz | cut -f1)

echo "DEVELOPMENT BUILD COMPLETE"