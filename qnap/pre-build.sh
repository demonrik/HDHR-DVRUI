#!/bin/sh
# Simple script to setup the qnap shared directory with the app

APP_DIR='../app'
SHARED_DIR='./shared'
CMD_CP='cp -R'
CMD_RM='rm -r'

case "$1" in
	prepare)
		# Copy the app folder
		$CMD_CP $APP_DIR $SHARED_DIR
	;;

	clean)
		# Remove the copied app folder
		$CMD_RM $SHARED_DIR/app
	;;

	*)
	echo "Usage: $0 {prepare|clean}"
	exit 1
esac  
