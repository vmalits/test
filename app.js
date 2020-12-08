let r = new Resumable({
    target: 'upload.php',
    testChunks: true,
    testMethod: 'GET'
});

r.assignBrowse(document.getElementById('browseButton'));

r.on('fileSuccess', function (file) {
    console.debug('fileSuccess', file);
});
r.on('fileProgress', function (file) {
    console.debug('fileProgress', file);
});
r.on('fileAdded', function (file, event) {
    r.upload();
    console.debug('fileAdded', event);
});
r.on('filesAdded', function (array) {
    r.upload();
    console.debug('filesAdded', array);
});
r.on('fileRetry', function (file) {
    console.debug('fileRetry', file);
});
r.on('fileError', function (file, message) {
    console.debug('fileError', file, message);
});
r.on('uploadStart', function () {
    console.debug('uploadStart');
});
r.on('complete', function () {
    console.debug('complete');
});
r.on('progress', function () {
    console.debug('progress');
});
r.on('error', function (message, file) {
    console.debug('error', message, file);
});
r.on('pause', function () {
    console.debug('pause');
});
r.on('cancel', function () {
    console.debug('cancel');
});


