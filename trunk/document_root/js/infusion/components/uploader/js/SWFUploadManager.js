fluid_1_1=fluid_1_1||{};(function($,fluid){var unbindSelectFiles=function(){var emptyFunction=function(){};SWFUpload.prototype.selectFile=emptyFunction;SWFUpload.prototype.selectFiles=emptyFunction};var prepareUpstreamOptions=function(that,uploader){that.returnedOptions={uploadManager:{type:uploader.options.uploadManager.type||uploader.options.uploadManager}}};var createAfterReadyHandler=function(that,uploader){return function(){var flashMovie=$("#"+uploader.uploadManager.swfUploader.movieName,uploader.container);var browseButton=uploader.locate("browseButton");fluid.tabindex(flashMovie,0);flashMovie.attr("role","button");flashMovie.attr("alt","Browse files button");if(that.isTransparent){flashMovie.addClass(that.options.styles.browseButtonOverlay);flashMovie.css("top",browseButton.position().top);flashMovie.css("left",browseButton.position().left)}}};var createFlash9MovieContainer=function(){var container=$("<div class='fl-uploader-flash9-container'></div>");var placeholder=$("<span></span>");var placeholderId=fluid.allocateSimpleId(placeholder);container.append(placeholder);$("body").append(container);return placeholderId};var setupForFlash9=function(that,uploader){that.returnedOptions.uploadManager.options={flashURL:that.options.flash9URL||undefined,flashButtonPeerId:createFlash9MovieContainer()}};var createEmptyPlaceholder=function(){var placeholder=$("<span></span>");fluid.allocateSimpleId(placeholder);return placeholder};var createButtonPlaceholder=function(browseButton){var placeholder=$("<span></span>");var placeholderId=fluid.allocateSimpleId(placeholder);browseButton.before(placeholder);unbindSelectFiles();return placeholderId};var setupForFlash10=function(that,uploader){var browseButton=uploader.locate("browseButton");fluid.tabindex(browseButton,-1);that.isTransparent=that.options.flashButtonAlwaysVisible?false:(!$.browser.msie||that.options.transparentEvenInIE);var peerId=that.isTransparent?createButtonPlaceholder(browseButton):fluid.allocateSimpleId(browseButton);that.returnedOptions.uploadManager.options={flashURL:that.options.flash10URL||undefined,flashButtonImageURL:that.isTransparent?undefined:that.options.flashButtonImageURL,flashButtonPeerId:peerId,flashButtonHeight:that.isTransparent?browseButton.outerHeight():that.options.flashButtonHeight,flashButtonWidth:that.isTransparent?browseButton.outerWidth():that.options.flashButtonWidth,flashButtonWindowMode:that.isTransparent?SWFUpload.WINDOW_MODE.TRANSPARENT:SWFUpload.WINDOW_MODE.OPAQUE,flashButtonCursorEffect:SWFUpload.CURSOR.HAND,listeners:{afterReady:createAfterReadyHandler(that,uploader),onUploadStart:function(){uploader.uploadManager.swfUploader.setButtonDisabled(true)},afterUploadComplete:function(){uploader.uploadManager.swfUploader.setButtonDisabled(false)}}}};fluid.swfUploadSetupDecorator=function(uploader,options){var that={};fluid.mergeComponentOptions(that,"fluid.swfUploadSetupDecorator",options);that.flashVersion=swfobject.getFlashPlayerVersion().major;prepareUpstreamOptions(that,uploader);if(that.flashVersion===9){setupForFlash9(that,uploader)}else{setupForFlash10(that,uploader)}return that};fluid.defaults("fluid.swfUploadSetupDecorator",{flashButtonAlwaysVisible:true,transparentEvenInIE:false,flashButtonImageURL:"../images/browse.png",flashButtonHeight:22,flashButtonWidth:106,styles:{browseButtonOverlay:"fl-uploader-browse-overlay"}});var swfUploadOptionsMap={uploadURL:"upload_url",flashURL:"flash_url",postParams:"post_params",fileSizeLimit:"file_size_limit",fileTypes:"file_types",fileTypesDescription:"file_types_description",fileUploadLimit:"file_upload_limit",fileQueueLimit:"file_queue_limit",flashButtonPeerId:"button_placeholder_id",flashButtonImageURL:"button_image_url",flashButtonHeight:"button_height",flashButtonWidth:"button_width",flashButtonWindowMode:"button_window_mode",flashButtonCursorEffect:"button_cursor",debug:"debug"};var swfUploadEventMap={afterReady:"swfupload_loaded_handler",onFileDialog:"file_dialog_start_handler",afterFileQueued:"file_queued_handler",onQueueError:"file_queue_error_handler",afterFileDialog:"file_dialog_complete_handler",onFileStart:"upload_start_handler",onFileProgress:"upload_progress_handler",onFileError:"upload_error_handler",onFileSuccess:"upload_success_handler"};var mapNames=function(nameMap,source,target){var result=target||{};for(var key in source){var mappedKey=nameMap[key];if(mappedKey){result[mappedKey]=source[key]}}return result};var mapEvents=function(that,nameMap,target){var result=target||{};for(var eventType in that.events){var fireFn=that.events[eventType].fire;var mappedName=nameMap[eventType];if(mappedName){result[mappedName]=fireFn}}result.upload_complete_handler=function(file){that.queueManager.finishFile(file);if(that.queueManager.shouldUploadNextFile()){that.swfUploader.startUpload()}else{if(that.queueManager.queue.shouldStop){that.swfUploader.stopUpload()}that.queueManager.complete()}};return result};var browse=function(that){if(that.queue.isUploading){return }if(that.options.fileQueueLimit===1){that.swfUploader.selectFile()}else{that.swfUploader.selectFiles()}};var stopUpload=function(that){that.queue.shouldStop=true;that.events.onUploadStop.fire()};var bindEvents=function(that){var fileStatusUpdater=function(file){fluid.find(that.queue.files,function(potentialMatch){if(potentialMatch.id===file.id){potentialMatch.filestatus=file.filestatus;return true}})};that.events.afterFileQueued.addListener(function(file){that.queue.addFile(file)});that.events.onFileStart.addListener(function(file){that.queueManager.startFile();fileStatusUpdater(file)});that.events.onFileProgress.addListener(function(file,currentBytes,totalBytes){var currentBatch=that.queue.currentBatch;var byteIncrement=currentBytes-currentBatch.previousBytesUploadedForFile;currentBatch.totalBytesUploaded+=byteIncrement;currentBatch.bytesUploadedForFile+=byteIncrement;currentBatch.previousBytesUploadedForFile=currentBytes;fileStatusUpdater(file)});that.events.onFileError.addListener(function(file,error){if(error===fluid.uploader.errorConstants.UPLOAD_STOPPED){that.queue.isUploading=false}else{if(that.queue.isUploading){that.queue.currentBatch.totalBytesUploaded+=file.size;that.queue.currentBatch.numFilesErrored++}}fileStatusUpdater(file)});that.events.onFileSuccess.addListener(function(file){if(that.queue.currentBatch.bytesUploadedForFile===0){that.queue.currentBatch.totalBytesUploaded+=file.size}fileStatusUpdater(file)});that.events.afterUploadComplete.addListener(function(){that.queue.isUploading=false})};var removeFile=function(that,file){that.queue.removeFile(file);that.swfUploader.cancelUpload(file.id);that.events.afterFileRemoved.fire(file)};var setupSwfUploadManager=function(that,events){that.events=events;that.queue=fluid.fileQueue();that.queueManager=fluid.fileQueue.manager(that.queue,that.events);that.swfUploadSettings=mapNames(swfUploadOptionsMap,that.options);mapEvents(that,swfUploadEventMap,that.swfUploadSettings);that.swfUploader=new SWFUpload(that.swfUploadSettings);bindEvents(that)};fluid.swfUploadManager=function(events,options){var that={};fluid.mergeComponentOptions(that,"fluid.swfUploadManager",options);fluid.mergeListeners(events,that.options.listeners);that.browseForFiles=function(){browse(that)};that.removeFile=function(file){removeFile(that,file)};that.start=function(){that.queueManager.start();that.swfUploader.startUpload()};that.stop=function(){stopUpload(that)};setupSwfUploadManager(that,events);return that};fluid.defaults("fluid.swfUploadManager",{uploadURL:"",flashURL:"../../../lib/swfupload/flash/swfupload.swf",flashButtonPeerId:"",postParams:{},fileSizeLimit:"20480",fileTypes:"*",fileTypesDescription:null,fileUploadLimit:0,fileQueueLimit:0,debug:false})})(jQuery,fluid_1_1);