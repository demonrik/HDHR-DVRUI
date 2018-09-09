#!/bin/sh

# Build the SPK
SPK_ROOT=$PWD
TMP_PKG_PATH=$PWD/tmp_pkg
TMP_SPK_PATH=$PWD/tmp_spk
BUILD_PATH=$PWD/build
UI_PATH=$SPK_ROOT/../app
VERSION=$(awk -F'=' '/^version/{print $2}' ./INFO | tr -d '"')
SPK_PKG_NAME=HDHR_DVRUI-$VERSION.spk

echo "Building the SPK"
echo "version: $VERSION"

#Check Package Exists

#copy files to tmp
mkdir $TMP_PKG_PATH
mkdir $TMP_PKG_PATH/web
mkdir $TMP_SPK_PATH
cp -R $UI_PATH/* $TMP_PKG_PATH/web

#Build the package
cd $TMP_PKG_PATH
tar cvfz $TMP_SPK_PATH/package.tgz *
cd $SPK_ROOT

#Move files ready for the SPK
cp -R scripts $TMP_SPK_PATH
cp icons/* $TMP_SPK_PATH
cp INFO $TMP_SPK_PATH

#Build SPK
cd $TMP_SPK_PATH
tar cvf $BUILD_PATH/$SPK_PKG_NAME *
cd $SPK_ROOT


