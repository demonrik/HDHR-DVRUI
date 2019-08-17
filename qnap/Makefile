BUILD_ROOT=$(PWD)
BUILD_BIN_PATH=$(BUILD_ROOT)/shared
BUILD_OUT_PATH=$(BUILD_ROOT)/build

# UI Params
UI_OUT_PATH=$(BUILD_ROOT)/shared/app
UI_IN_PATH=$(BUILD_ROOT)/../app
UI_VARS=vars.php

QPKG_VERSION=$(shell awk -F'=' '/DVRUI_version/{print $$2}' ../app/vars.php | tr -d '"' | tr -d ';')
QPKG_BIN=qbuild
QPKG_NAME=HDHR-DVRUI_$(QPKG_VERSION).qpkg

CHMOD_BIN=chmod
CHMOD_OPTS=a+x
SED_BIN=sed 

$(BUILD_OUT_PATH)/$(QPKG_NAME): $(BUILD_BIN_PATH)/$(DVR_BIN) all_wrappers $(UI_OUT_PATH)/$(UI_VARS)
	@echo "Update Version for QPKG"
	@$(SED_BIN) -i "s!\(QPKG_VER\s*=\).*!\1\"$(QPKG_VERSION)\"!" ./qpkg.cfg
	@echo "Building QPackage"
	@$(QPKG_BIN)

$(UI_OUT_PATH)/$(UI_VARS):
	@echo "Prepping UI Folder"
	@mkdir $(UI_OUT_PATH)
	@cp -R $(UI_IN_PATH)/* $(UI_OUT_PATH)
	
all: $(BUILD_OUT_PATH)/$(QPKG_NAME)

clean:
	@rm -rf $(UI_OUT_PATH) \
     $(BUILD_OUT_PATH)/$(QPKG_NAME) \
     $(BUILD_OUT_PATH)/$(QPKG_NAME).md5

.PHONY: all clean

.DEFAULT:
	@echo "You need to do something"