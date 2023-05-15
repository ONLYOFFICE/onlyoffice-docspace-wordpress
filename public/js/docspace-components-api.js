(function () {
    if (!window.DocSpaceComponent) window.DocSpaceComponent = {};

    var scriptTag = null;
    window.DocSpaceComponent.initScript = function (docSpaceUrl = DocSpaceComponent.docSpaceUrl) {
        return new Promise((resolve, reject) => {
            if (window.DocSpace || scriptTag) return resolve();
            docSpaceUrl += docSpaceUrl.endsWith("/") ? "" : "/"
            scriptTag = document.createElement("script");
            scriptTag.src = docSpaceUrl + "static/scripts/api.js";
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

    window.DocSpaceComponent.renderError = function (id, error) {
        const target = document.getElementById(id);

        let errorDiv = document.createElement('div');
        errorDiv.className="error-stub";

        if (id.endsWith("selector")) {
            errorDiv.classList.add("selector");
        }
        if (id.startsWith("onlyoffice-docpace-block")) {
            errorDiv.classList.add("viewer");
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

    window.DocSpaceComponent.getCredentials = function (сredentialUrl) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", сredentialUrl, false);
        xhr.send();

        if (xhr.status === 200) {
            return JSON.parse(xhr.responseText);
        }

        return null;
    }

    window.DocSpaceComponent.initLoginDocSpace = function (frameId, onSuccess, onError) {
        DocSpace.SDK.initFrame({
            frameId: frameId,
            mode: "system",
            events: {
                onAppReady: async function() {
                    if (!window.DocSpaceComponent.onAppReady) { // ToDo: Delete after fixes
                        window.DocSpaceComponent.onAppReady = true;

                        const userInfo = await DocSpace.SDK.frames[frameId].getUserInfo();

                        if (userInfo && userInfo.email === DocSpaceComponent.user.email){
                            onSuccess();
                        } else {
                            const hash = DocSpaceComponent.getCredentials(DocSpaceComponent.сredentialUrl);

                            DocSpace.SDK.frames[frameId].login(DocSpaceComponent.user.email, hash)
                                .then(function(response) {
                                    //ToDO: check response, need fix response
                                    onSuccess();
                                }
                            );
                        } 
                    }
                },
                onAppError: async function() {
                    onError();
                }
            }
        });
    };
})();
