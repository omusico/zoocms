jQuery(document).ready(function () {
    var myUpload = fluid.progressiveEnhanceableUploader(".flc-uploader", ".fl-progEnhance-basic", {
        uploadManager: {
		    type: "fluid.swfUploadManager",

		    options: {
		       // Set the uploadURL to the URL for posting files to your server.
		       uploadURL: "/Filemanager/file/performupload",

		       // This option points to the location of the SWFUpload Flash object that ships with Fluid Infusion.
		       flashURL: "/js/infusion/lib/swfupload/flash/swfupload.swf"
			}
		},

        listeners: {
            onFileSuccess: function (file, serverData){
                // example assumes that the server code passes the new image URL in the serverData
                jQuery('#image-space').append('<img src="' + serverData + '" alt="' + file.name +'" />');
            },

            afterUploadComplete: function () {
                var myNextStepURI = "/Filemanager/";

                // first check to see if the file queue is empty and there haven't been any errors
                if (myUpload.uploadManager.queue.getReadyFiles().length === 0 && myUpload.uploadManager.queue.getErroredFiles().length === 0) {
                    // then go someplace
                    window.location.href = myNextStepURI;
                }
            }
        },

        decorators: [{
            type: "fluid.swfUploadSetupDecorator",
            options: {
                // This option points to the location of the Browse Files button used with Flash 10 clients.
                flashButtonImageURL: "/js/infusion/components/uploader/images/browse.png"
            }
        }]
    });
});