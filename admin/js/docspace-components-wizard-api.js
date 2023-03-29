(function () {
    if (!window.DocSpaceWizardComponent) window.DocSpaceWizardComponent = {};

    var scriptTag = null;
    window.DocSpaceWizardComponent.initScript = function () {
        return new Promise((res) => {
            if (window.DocSpace || scriptTag) res();
            scriptTag = document.createElement("script");
            scriptTag.src = DocSpaceWizardComponent.docSpaceUrl + "static/scripts/api.js";
            scriptTag.async = true;
            document.body.appendChild(scriptTag);

            scriptTag.addEventListener('load', () => {
                res();
            })
        });
    };

    window.DocSpaceWizardComponent.init = async function (config) {
        await DocSpaceWizardComponent.initScript();
        DocSpace.initFrame(config);
    }

    document.addEventListener('DOMContentLoaded', function () {
        DocSpaceWizardComponent.init({
            "frameId": "ds-wizard-frame"
        });
    });
})();
