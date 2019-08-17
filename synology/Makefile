BUILD_ROOT=$(PWD)
SPK_VER=$(shell awk -F'=' '/DVRUI_version/{print $$2}' ../app/vars.php | tr -d '"' | tr -d ';')
SPK_NAME=HDHR-DVRUI_$(SPK_VER).spk
SPK_MD5=$(SPK_NAME).md5

# Temporary Folders
TMP_PKG_PATH=$(BUILD_ROOT)/tmp_pkg
TMP_PKG_NAME=package.tgz
TMP_SPK_PATH=$(BUILD_ROOT)/tmp_spk

#UI Params
UI_OUT_PATH=$(BUILD_ROOT)/shared/app
UI_IN_PATH=$(BUILD_ROOT)/../app
UI_VARS=vars.php

SED_BIN=sed 
CHMOD_BIN=chmod
CHMOD_OPTS=a+x
BUILD_BIN_PATH=$(BUILD_ROOT)/shared
BUILD_OUT_PATH=$(BUILD_ROOT)/build

$(BUILD_OUT_PATH)/$(SPK_NAME): $(TMP_SPK_PATH)/$(TMP_PKG_NAME)
	@echo "Update Version for SPK"
	@$(SED_BIN) -i "s!\(version\s*=\).*!\1\"$(SPK_VER)\"!" ./INFO
	@cp -R scripts $(TMP_SPK_PATH)
	@cp icons/* $(TMP_SPK_PATH)
	@cp INFO $(TMP_SPK_PATH)
	@echo "Building $(SPK_NAME)"
	@tar cf $(BUILD_OUT_PATH)/$(SPK_NAME) -C $(TMP_SPK_PATH) INFO PACKAGE_ICON_256.PNG PACKAGE_ICON.PNG package.tgz scripts


$(TMP_SPK_PATH)/$(TMP_PKG_NAME): $(TMP_BIN_PATH)/$(DVR_BIN) prep_dirs
	@cp -R $(UI_IN_PATH)/* $(TMP_PKG_PATH)/web
	@tar cfz $(TMP_SPK_PATH)/package.tgz -C $(TMP_PKG_PATH) web
	
prep_dirs:
	@echo "Creating Temporary Folders"
	@mkdir $(TMP_PKG_PATH)
	@mkdir $(TMP_PKG_PATH)/web
	@mkdir $(TMP_SPK_PATH)

all: $(BUILD_OUT_PATH)/$(SPK_NAME)

clean:
	@rm -rf $(TMP_PKG_PATH) \
			$(TMP_SPK_PATH) \
			$(BUILD_OUT_PATH)/$(SPK_NAME)

.PHONY: all clean all_wrappers prep_dirs

.DEFAULT:
	@echo "You need to do something"