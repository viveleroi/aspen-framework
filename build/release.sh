#!/bin/sh

# THIS SCRIPT WAS DESIGNED TO PREPARE ASPEN FRAMEWORK FOR A PRODUCTION RELEASE

if [ $# -ne 3 ]; then
     echo 1>&2 Usage: 1.0 rc1 Release_Candidate
     exit 0
fi

if [ $2 = "final" ]; then
     versname=$1
else 
     versname=$1$2
fi

# remove any existing exports
rm -rf trunk

# make a branch for this release
export SVN_EDITOR=vi
svn copy http://svn.trellisdevelopment.com/aspen-framework/trunk http://svn.trellisdevelopment.com/aspen-framework/branches/$versname

# checkout the latest code from trunk
svn co http://svn.trellisdevelopment.com/aspen-framework/trunk
cd trunk

# get the svn revision number and create a RELEASE file
svnvers=`svnversion .`

# add in revision to app.default.config.php
sed -e "s/application_build'] = ''/application_build'] = '$svnvers'/g" app.default.config.php > adc-new.php
mv adc-new.php app.default.config.php

#remove support dirs
rm -rf tests
rm -rf build

# remove all .svn directories
find . -name .svn -exec rm -rf {} \;

# add in revision to bootstrap define
cd system
sed -e "s/define('FRAMEWORK_REV', '')/define('FRAMEWORK_REV', '$svnvers')/g" bootstrap.php > bootstrap-new.php
mv bootstrap-new.php bootstrap.php
cd ..

# make tarball
tar czvf af-temp.tar.gz *
mv af-temp.tar.gz ../aspen-$versname.tar.gz
cd ..
rm -rf latest
mv trunk latest

# get file size
fsize=$(du -ks aspen-$versname.tar.gz | cut -f1)

# run phpdoc
phpdoc/phpdoc -c /BUILD/phpdoc.ini

echo "RELEASE BUILD COMPLETE, LOADING TO AMAZON-S3"

# send file to amazon bucket
s3cmd put --acl-public aspen-$versname.tar.gz s3://aspen-framework/aspen-$versname.tar.gz

# move files
mv aspen-$versname.tar.gz builds/aspen-$versname.tar.gz

echo "LOADED TO S3, ADDING NEW RELEASE INFO TO MySQL"

mysql -uroot -pURQU9UWpVABgS9Zr3RXhIhxno -D aspen -e "INSERT INTO releases (file_name,file_size,version_number,build_number,release_type,release_timestamp) VALUES ('aspen-$versname.tar.gz','$fsize','$1 $2','$versname','$3','$(date +"%F %T")');"

echo "RELEASE COMPLETE"
