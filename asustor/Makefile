BUILD_ROOT=$(PWD)
APKG_NAME=HDHomeRunDVR
APKG_VER=$(shell awk -F':' '/version/{print $$2}' CONTROL/config.json | tr -d '",')
APKG_EXT=apk
APKG_FULLNAME=$(APKG_NAME)-$(APKG_VER).$(APKG_EXT)

# Temporary Folders
TMP_PKG_PATH=$(BUILD_ROOT)/$(APKG_NAME)

# UI Folders
UI_OUT_PATH=$(TMP_PKG_PATH)/www
UI_IN_PATH=$(BUILD_ROOT)/../common/php/simpleui

# APKG Tools
APKG_ZIP=APKG_Utilities_2.0.zip
APKG_URL=https://developer.asustor.com/document
APKG_TOOLS_PATH=$(BUILD_ROOT)/APKG_Utilities_2.0
APKG_BIN=apkg-tools.py
APKG_OPTION=create

# DVR Parameters - update if SiliconDust changes the values
DVR_BIN=hdhomerun_record_linux
DVR_LINK=http://download.silicondust.com/hdhomerun/hdhomerun_record_linux_beta

# Wrapper Parameters
WRAPPER_BIN_ARM7=hdhr_wrapper_arm7
WRAPPER_BIN_ARM8=hdhr_wrapper_arm8
WRAPPER_BIN_i686=hdhr_wrapper_i686
WRAPPER_BIN_X86_64=hdhr_wrapper_x86_64
WRAPPER_REPO_LINK=http://www.irish-networx.com/hdhr_wrapper

# Update this with any additional WGET parameters you need to use.. or place in local .wgetrc
WGET_BIN=wget
WGET_DVR_OPTS=-q -O $(BUILD_BIN_PATH)/$(DVR_BIN)
WGET_WRAPPER_OPTS=-q -P $(BUILD_BIN_PATH)
WGET_APKG_OPTS=-q -P $(BUILD_ROOT)

CHMOD_BIN=chmod
CHMOD_OPTS=a+x
BUILD_BIN_PATH=$(BUILD_ROOT)/bin
BUILD_OUT_PATH=$(BUILD_ROOT)

$(BUILD_OUT_PATH)/$(APKG_FULLNAME): prep_temp
	@echo "Build APKG"
	$(APKG_TOOLS_PATH)/$(APKG_BIN) $(APKG_OPTION) $(TMP_PKG_PATH)

$(BUILD_BIN_PATH)/$(DVR_BIN):
	@echo "Fetching $(DVR_BIN)"
	@$(WGET_BIN) $(WGET_DVR_OPTS) $(DVR_LINK)
	@$(CHMOD_BIN) $(CHMOD_OPTS) $@

$(BUILD_BIN_PATH)/$(WRAPPER_BIN_ARM7):
	@echo "Fetching $(WRAPPER_BIN_ARM7)"
	@$(WGET_BIN) $(WGET_WRAPPER_OPTS) $(WRAPPER_REPO_LINK)/$(WRAPPER_BIN_ARM7)
	@$(CHMOD_BIN) $(CHMOD_OPTS) $@
	
$(BUILD_BIN_PATH)/$(WRAPPER_BIN_ARM8):
	@echo "Fetching $(WRAPPER_BIN_ARM8)"
	@$(WGET_BIN) $(WGET_WRAPPER_OPTS) $(WRAPPER_REPO_LINK)/$(WRAPPER_BIN_ARM8)
	@$(CHMOD_BIN) $(CHMOD_OPTS) $@

$(BUILD_BIN_PATH)/$(WRAPPER_BIN_i686):
	@echo "Fetching $(WRAPPER_BIN_i686)"
	@$(WGET_BIN) $(WGET_WRAPPER_OPTS) $(WRAPPER_REPO_LINK)/$(WRAPPER_BIN_i686)
	@$(CHMOD_BIN) $(CHMOD_OPTS) $@

$(BUILD_BIN_PATH)/$(WRAPPER_BIN_X86_64):
	@echo "Fetching $(WRAPPER_BIN_X86_64)"
	@$(WGET_BIN) $(WGET_WRAPPER_OPTS) $(WRAPPER_REPO_LINK)/$(WRAPPER_BIN_X86_64)
	@$(CHMOD_BIN) $(CHMOD_OPTS) $@

tools:
	@echo "Getting latest APKG Tools"
	@$(WGET_BIN) $(WGET_APKG_OPTS) $(APKG_URL)/$(APKG_ZIP)
	@unzip $(BUILD_ROOT)/$(APKG_ZIP)
	@$(CHMOD_BIN) $(CHMOD_OPTS) $(APKG_TOOLS_PATH)/$(APKG_BIN)

prep_temp: all_wrappers tools $(BUILD_BIN_PATH)/$(DVR_BIN)
	@echo "Creating Temp Folder"
	@mkdir $(TMP_PKG_PATH)
	@cp -R bin $(TMP_PKG_PATH)
	@cp -R etc $(TMP_PKG_PATH)
	@cp -R CONTROL $(TMP_PKG_PATH)
	@cp -R www $(TMP_PKG_PATH)
	@cp -R $(UI_IN_PATH)/* $(UI_OUT_PATH)

all_wrappers: $(BUILD_BIN_PATH)/$(WRAPPER_BIN_ARM7) \
     					$(BUILD_BIN_PATH)/$(WRAPPER_BIN_i686) \
     					$(BUILD_BIN_PATH)/$(WRAPPER_BIN_X86_64)
	

all: $(BUILD_OUT_PATH)/$(APKG_FULLNAME)

clean:
	@rm -rf $(BUILD_BIN_PATH)/$(DVR_BIN) \
     $(BUILD_BIN_PATH)/$(WRAPPER_BIN_ARM7) \
     $(BUILD_BIN_PATH)/$(WRAPPER_BIN_i686) \
     $(BUILD_BIN_PATH)/$(WRAPPER_BIN_X86_64) \
     $(APKG_TOOLS_PATH) \
     $(BUILD_ROOT)/$(APKG_ZIP) \
     $(TMP_PKG_PATH)

.PHONY: all clean all_wrappers tools prep_tmp
