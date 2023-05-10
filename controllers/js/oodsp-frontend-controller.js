(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var frames = document.getElementsByClassName("onlyoffice-docpace-block");

        DocSpaceComponent.initScript().then(function() {
            for (var frame of [...frames]) {;
                DocSpace["ds-frame"].initFrame(JSON.parse(frame.dataset.config));
            }
        }).catch(function() {
            for (var frame of frames) {;
                DocSpaceComponent.renderError(frame.id, { message: "Portal unavailable! Please contact the administrator!" });
            }
        });
    });
})();