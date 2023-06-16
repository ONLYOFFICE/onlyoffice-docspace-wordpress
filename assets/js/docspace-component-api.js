(function () {
    if (!window.DocSpaceComponent) window.DocSpaceComponent = {};

    var scriptTag = null;
    window.DocSpaceComponent.initScript = function (docSpaceUrl = DocSpaceComponent.url) {
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

        if (id.includes("selector")) {
            errorDiv.classList.add("selector");
        } else if (id.includes("manager")) {
        } else {
            errorDiv.classList.add("viewer");
        }

        errorDiv.innerHTML = `
            <div class="unavailable-header">
                <img src="${DocSpaceComponent.images.onlyoffice}" />
                <span><b>ONLYOFFICE</b> DocSpace</span>
            </div>
            <img class="unavailable-icon" src="${DocSpaceComponent.images.unavailable}" />
            <div class="unavailable-message">${error.message}</div>
        `;

        target.innerHTML = "";
        target.appendChild(errorDiv);
    }

    window.DocSpaceComponent.oodspCredentials = function (hash = null) {
        var xhr = new XMLHttpRequest();
        var postData = "action=oodsp_credentials";
        
        if (DocSpaceComponent.isPublic) {
            postData += "&is_public=true";
        }
        
        if (hash) {
            postData += "&hash=" + hash;
        }

        xhr.open("POST", DocSpaceComponent.ajaxUrl, false);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
        xhr.send(postData);

        if (xhr.status === 200) {
            return xhr.responseText || null;
        }

        return null;
    }

    window.DocSpaceComponent.initLoginDocSpace = function (frameId, password, onSuccess, onError) {
        DocSpace.SDK.initSystem({
            frameId: frameId,
            events: {
                onAppReady: async function() {
                    const userInfo = await DocSpace.SDK.frames[frameId].getUserInfo();

                    if (userInfo && userInfo.email === DocSpaceComponent.currentUser){
                        onSuccess();
                    } else {
                        var hash = null;

                        if (password) {
                            const hashSettings = await DocSpace.SDK.frames[frameId].getHashSettings();
                            hash = await DocSpace.SDK.frames[frameId].createHash(password.trim(), hashSettings);
                        } else {
                            hash = DocSpaceComponent.oodspCredentials();
                        }

                        if (hash === null || hash.length === "") {
                            DocSpace.SDK.frames[frameId].destroyFrame();
                            wp.oodsp.login(frameId, DocSpaceComponent.url, DocSpaceComponent.currentUser, null, function (password) {
                                window.DocSpaceComponent.initLoginDocSpace(frameId, password, onSuccess, onError);
                            });
                            return;
                        }

                        DocSpace.SDK.frames[frameId].login(DocSpaceComponent.currentUser, hash)
                            .then(function(response) {
                                if(response.status && response.status !== 200) {
                                    DocSpace.SDK.frames[frameId].destroyFrame();
                                    wp.oodsp.login(
                                        frameId,
                                        DocSpaceComponent.url,
                                        DocSpaceComponent.currentUser,
                                        true, 
                                        function (password) {
                                            window.DocSpaceComponent.initLoginDocSpace(frameId, password, onSuccess, onError);
                                        }
                                    );
                                    return;
                                }

                                if (password) {
                                    DocSpaceComponent.oodspCredentials(hash);
                                }
                                onSuccess();
                            }
                        );
                    } 
                },
                onAppError: async function() {
                    onError();
                }
            }
        });
    };

    window.DocSpaceComponent.initPublicDocSpace = function (frameId, onSuccess, onError) {
        DocSpace.SDK.initFrame({
            frameId: frameId,
            mode: "system",
            events: {
                onAppReady: async function() {
                    console.log("onAppReady: initPublicDocSpace");
                    const userInfo = await DocSpace.SDK.frames[frameId].getUserInfo();

                    if (userInfo && userInfo.email === DocSpaceComponent.currentUser) {
                        onSuccess();
                    } else {
                        const hash = DocSpaceComponent.oodspCredentials();

                        DocSpace.SDK.frames[frameId].login(DocSpaceComponent.currentUser, hash)
                            .then(function(response) {
                                //ToDO: check response, need fix response
                                // onError: function () {
                                    // DocSpaceComponent.renderLoginWindow();
                                // }

                                onSuccess();
                            }
                        );
                    } 
                },
                onAppError: async function() {
                    onError();
                }
            }
        });
    };
})();
