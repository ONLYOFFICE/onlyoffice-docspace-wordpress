(function () {
    if (!window.DocSpaceComponent) window.DocSpaceComponent = {};

    var scriptTag = null;
    window.DocSpaceComponent.initScript = function (docSpaceUrl = DocSpaceComponent.docSpaceUrl) {
        return new Promise((resolve, reject) => {
            if (window.DocSpace || scriptTag) return resolve();
            docSpaceUrl += docSpaceUrl.endsWith("/") ? "" : "/"
            scriptTag = document.createElement("script");
            scriptTag.src = docSpaceUrl + "static/scripts/apisds.js";
            scriptTag.async = true;
            document.body.appendChild(scriptTag);

            scriptTag.addEventListener('load', () => {
                return resolve();
            })
            scriptTag.addEventListener('error', () => {
                return reject();
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

    window.DocSpaceComponent.renderError = function (id, error) {
        const target = document.getElementById(id);

        let errorDiv = document.createElement('div');
        errorDiv.className="error-stub";

        if (id.endsWith("selector")) {
            errorDiv.classList.add("selector");
        }

        errorDiv.innerHTML = `
            <div class="unavailable-header">
                <img src="${DocSpaceComponent.wp_plugin_url}/onlyoffice-docspace-wordpress/public/images/onlyoffice.svg" />
                <span><b>ONLYOFFICE</b> DocSpace</span>
            </div>
            <img class="unavailable-icon" src="${DocSpaceComponent.wp_plugin_url}/onlyoffice-docspace-wordpress/public/images/unavailable.svg" />
            <div class="unavailable-message">${error.message}</div>
        `;

        target.innerHTML = "";
        target.appendChild(errorDiv);
    }

    document.addEventListener('DOMContentLoaded', function () {
        var frames = document.getElementsByClassName("ds-frame-view");

        for (var frame of frames) {
            console.log(JSON.parse(frame.dataset.config));
            DocSpaceComponent.init(JSON.parse(frame.dataset.config));
        }
    });
})();
