(function () {
    if (!window.DocSpaceComponent) window.DocSpaceComponent = {};

    var scriptTag = null;
    window.DocSpaceComponent.initScript = function () {
        return new Promise((res) => {
            if (window.DocSpace || scriptTag) return res();
            scriptTag = document.createElement("script");
            scriptTag.src = DocSpaceComponent.docSpaceUrl + "static/scripts/api.js";
            scriptTag.async = true;
            document.body.appendChild(scriptTag);

            scriptTag.addEventListener('load', () => {
                return res();
            })
        });
    };

    window.DocSpaceComponent.generateId = function () {
        return Math.floor((1 + Math.random()) * 0x10000000)
            .toString(16)
            .substring(1);
    }

    window.DocSpaceComponent.init = async function (config) {
        await DocSpaceComponent.initScript();
        DocSpace.initFrame(config);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var frames = document.getElementsByClassName("ds-frame-view");

        for (var frame of frames) {
            console.log(JSON.parse(frame.dataset.config));
            DocSpaceComponent.init(JSON.parse(frame.dataset.config));
        }
    });
})();
