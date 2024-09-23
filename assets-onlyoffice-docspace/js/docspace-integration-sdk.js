/**
 *
 * (c) Copyright Ascensio System SIA 2024
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

(function () {
    const DOCSPACE_API_URL = "static/scripts/sdk/1.0.1/api.js";

    const initScript = async (id, url) => {
        return new Promise((resolve, reject) => {
            try {
                // If DocSpace is defined return resolve.
                if (window.DocSpace) return resolve(null);

                if(url == null) {
                    return reject("DocSpace Url is not defined!");
                }

                const existedScript = document.getElementById(id);

                if (existedScript) {
                    // If the script element is found, wait for it to load.
                    let intervalHandler = setInterval(() => {
                        const loading = existedScript.getAttribute("loading");
                        if (loading) {
                            // If the download is not completed, continue to wait.
                            return;
                        } else {
                            // If the download is completed, stop the wait.
                            clearInterval(intervalHandler);

                            // If DocSpace is defined, after loading return resolve.
                            if (window.DocSpace) return resolve(null);

                            // If DocSpace is not defined, delete the existing script and create a new one.
                            const script = _createScriptTag(id , url, resolve, reject);
                            existedScript.remove();
                            document.body.appendChild(script);
                        }
                    }, 500);
                } else {
                    // If the script element is not found, create it.
                    const script = _createScriptTag(id, url, resolve, reject);
                    document.body.appendChild(script);
                }
            } catch (e) {
                console.error(e);
            }
        });
    };

    const loginByPassword = function (frameId, email, password, onSuccessLogin, onUnSuccessLogin, onAppError) {
        loginByPasswordHash(
            frameId,
            email,
            async function(email) {
                const hashSettings = await DocSpace.SDK.frames[frameId].getHashSettings();
                const passwordHash = await DocSpace.SDK.frames[frameId].createHash(password.trim(), hashSettings);

                return passwordHash;
            },
            onSuccessLogin,
            onUnSuccessLogin,
            onAppError
        )
    };

    const loginByPasswordHash = function (frameId, email, onRequestPasswordHash, onSuccessLogin, onUnSuccessLogin, onAppError) {
        DocSpace.SDK.initSystem({
            frameId: frameId,
            width: "100%",
            height: "100%",
            events: {
                onAppReady: async function() {
                    const userInfo = await DocSpace.SDK.frames[frameId].getUserInfo();

                    if (userInfo && userInfo.email === email){
                        onSuccessLogin();
                    } else {
                        const passwordHash = await onRequestPasswordHash(email);

                        if (passwordHash == null || passwordHash.length <= 0) {
                            DocSpace.SDK.frames[frameId].destroyFrame();
                            onUnSuccessLogin();
                            return;
                        }

                        DocSpace.SDK.frames[frameId].login(email, passwordHash)
                            .then(function(response) {
                                if(response.status && response.status !== 200) {
                                    DocSpace.SDK.frames[frameId].destroyFrame();
                                    onUnSuccessLogin();
                                    return;
                                }

                                onSuccessLogin(passwordHash);
                            }
                        );
                    } 
                },
                onAppError: async function() {
                    if (onAppError) {
                        onAppError();
                    }
                }
            }
        });
    };

    const logout = function (frameId, onLogout, onAppError) {
        DocSpace.SDK.initSystem({
            frameId: frameId,
            width: "100%",
            height: "100%",
            events: {
                onAppReady: async function() {
                    const userInfo = await DocSpace.SDK.frames[frameId].getUserInfo();

                    if (userInfo){
                        DocSpace.SDK.frames[frameId].logout().then(function() {
                            onLogout();
                        });
                    } else {
                        onLogout();
                    }
                },
                onAppError: async function() {
                    if (onAppError) {
                        onAppError();
                    }
                }
            }
        });
    };
  

    const _createScriptTag = (id, url, resolve, reject) => {
        const script = document.createElement("script");
        
        script.id = id;
        script.type = "text/javascript";
        script.src = url.endsWith("/") ? url + DOCSPACE_API_URL : url + "/" + DOCSPACE_API_URL;
        script.async = true;

        script.onload = () => {
            // Remove attribute loading after loading is complete.
            script.removeAttribute("loading");
            resolve(null);
        };
        script.onerror = (error) => {
            // Remove attribute loading after loading is complete.
            script.removeAttribute("loading");
            reject(error);
        };

        script.setAttribute("loading", "");

        return script;
    };

    window.DocspaceIntegrationSdk = window.DocspaceIntegrationSdk || {
        "initScript": initScript,
        "loginByPassword": loginByPassword,
        "loginByPasswordHash": loginByPasswordHash,
        "logout": logout
    };
})();