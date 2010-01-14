#!/bin/sh

# THIS SCRIPT WAS DESIGNED TO PREPARE ASPEN FRAMEWORK FOR A PRODUCTION RELEASE

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


# OFFICIAL RELEASE STAGE HERE

# comment this out if pushing a true release
exit 0

cd ..
rm -rf latest
mv aspen-framework latest

# get file size
fsize=$(du -ks aspen-$gitvers.tar.gz | cut -f1)

# run phpdoc
phpdoc/phpdoc -c /BUILD/phpdoc.ini

echo "RELEASE BUILD COMPLETE, LOADING TO AMAZON-S3"

# send file to amazon bucket
s3cmd put --acl-public aspen-$gitvers.tar.gz s3://aspen-framework/aspen-$gitvers.tar.gz

# move files
mv aspen-$versname.tar.gz builds/aspen-$gitvers.tar.gz

echo "Loading to Amazon S3"

echo "INSERT INTO releases (file_name,file_size,version_number,release_type,release_timestamp) VALUES ('aspen-$gitvers.tar.gz','$fsize','$gitvers','$2','$(date +"%F %T")');" > release.sql

echo "Please run the release.sql file on the aspen website."

echo "RELEASE COMPLETE"