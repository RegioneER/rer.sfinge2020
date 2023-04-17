var routeUrl = document.getElementById("dropzone-custom").getAttribute("data-route");
var routeConcatUrl = document.getElementById("dropzone-custom").getAttribute("data-route-concat");
var fileInfoUrl = document.getElementById("dropzone-custom").getAttribute("data-file-info");

// Non uso direttamente l'ID per essere compatibile con le diverse sezioni da cui viene chiamato.
$("[id ^='documento_'][id $='_tipologia_documento']").change(function() {
    fileInfoUrl = fileInfoUrl.replace('id_documento', $("[id ^='documento_'][id $='_tipologia_documento']").find(":selected").val());
});

Dropzone.autoDiscover = false;
$(document).ready(function() {
    $("#myDrop").dropzone({
            maxFiles: 1,
            acceptedFiles: '.mp4',
            maxFilesize: 350, // in Mb
            chunking: true,
            forceChunking: true,
            chunkSize: 5*1024*1024,
            timeout: 0,

            init: function() {
                this.on("error", function(file, errorMessage) {
                    alert("error : " + errorMessage );
                });
                this.on("maxfilesexceeded", function(file) {
                    alert("error : " + errorMessage );
                });
                this.on("sending", function(file, xhr, formData) {
                    formData.append("tipologiaDocumento", $("[id ^='documento_'][id $='_tipologia_documento']").find(":selected").val());
                });
                this.on('addedfile', function (file, xhr, formData) {
                    $.ajax({
                        url: fileInfoUrl,
                        async: false,
                        success: function (res) {
                            this.options.acceptedFiles = res.estensioni;
                            this.options.maxFilesize = res.dimensione_massima;
                        }.bind(this)
                    });
                });
            },

            chunksUploaded: function (file, done) {
                var currentFile = file;

                // This calls server-side code to merge all chunks for the currentFile
                $.ajax({
                    url: routeConcatUrl,
                    method: "POST",
                    data: {
                        dzuuid: currentFile.upload.uuid,
                        dztotalchunkcount: currentFile.upload.totalChunkCount,
                        filename: currentFile.name,
                        tipologiaDocumento: $("[id ^='documento_'][id $='_tipologia_documento']").find(":selected").val(),
                        descrizioneDocumento: $("[id ^='documento_'][id $='_descrizione']").val(),
                    },
                    success: function (data) {
                        if (data.status == 'error') {
                            $(currentFile.previewElement).addClass("dz-error").find('.dz-error-message').text(data.msg);
                        } else {
                            currentFile.recordId = data.recordId;
                            currentFile.downloadUrl = data.downloadUrl;
                            done();
                            window.location.href = routeUrl;
                        }
                    },
                    error: function (msg) {
                        currentFile.accepted = false;
                        $(currentFile.previewElement).addClass("dz-error").find('.dz-error-message').text(msg.responseText);
                    }
                });
            }
        }
    );
});