/*
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

import {
    useBlockProps,
    InspectorControls,
    HeightControl,
    BlockControls
} from '@wordpress/block-editor';
import {
    Button,
    Placeholder,
    Modal,
    PanelBody,
    MenuItem,
    NavigableMenu,
    ToolbarButton,
    ToolbarGroup,
    Dropdown,
    SelectControl
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { blockStyle, onlyofficeIcon } from "./index";
import { __ } from '@wordpress/i18n';
import { getIconByType, getPublicIconByType } from "./icons";

const Edit = ({ attributes, setAttributes }) => {
    const [isOpen, setOpen] = useState( false );
    const [modalConfig, setModalConfig] = useState( {} );
    const [showDefaultIcon, setShowDefaultIcon] = useState( false );

    const themes = [
        {
            label: __("Light", "onlyoffice-docspace-plugin"),
            value: "Base"
        },
        {
            label: __("Dark", "onlyoffice-docspace-plugin"),
            value: "Dark"
        }
    ];

    const editorTypes = [
        {
            label: __("Embedded", "onlyoffice-docspace-plugin"),
            value: "embedded"
        },
        {
            label: __("Editor", "onlyoffice-docspace-plugin"),
            value: "desktop"
        }
    ];

    const script = () => {
        if (isOpen) {
            wp.oodsp.initLoginManager(
                "oodsp-selector-frame",
                function() {
                    DocSpace.SDK.initFrame(modalConfig);
                }
            );
        }
    };

    useEffect(script, [isOpen]);

    const onSelectRoomCallback = (event) => {
        Object.keys(attributes).forEach((key) => {
            if (["roomId", "fileId", "name", "icon", "requestToken", "editorType"].includes(key)) {    
                delete attributes[key];
            }
        });

        const requestTokens = event[0].requestTokens;
        const requestToken = requestTokens ? requestTokens[0].requestToken : null;

        setAttributes({
            roomId: event[0].id,
            name: event[0].label,
            icon: event[0].icon
        });

        if (requestToken) {
            setAttributes({
                requestToken: requestToken
            });
        }

        DocSpace.SDK.frames["oodsp-selector-frame"].destroyFrame();
        setOpen(false);
    }

    const onSelectFileCallback = (event) => {
        Object.keys(attributes).forEach((key) => {
            if (["roomId", "fileId", "name", "icon", "requestToken"].includes(key)) {    
                delete attributes[key];
            }
        });

        const requestTokens = event.requestTokens;
        const requestToken = requestTokens ? requestTokens[0].requestToken : null;

        setAttributes({
            fileId: event.id,
            name: event.title,
            icon: event.icon
        });

        if (requestToken) {
            setAttributes({
                requestToken: requestToken
            });
        }

        DocSpace.SDK.frames["oodsp-selector-frame"].destroyFrame();
        setOpen(false);
    }

    const onCloseCallback = () => {
        DocSpace.SDK.frames["oodsp-selector-frame"].destroyFrame();
        setOpen(false);
    }

    const openModal = (event) => {
        const mode = event.target.dataset.mode || null;
        var onSelectCallback = null;

        switch (mode) {
            case "room-selector":
                onSelectCallback = onSelectRoomCallback;
                break;
            case "file-selector":
                onSelectCallback = onSelectFileCallback;
                break;
        }

        setModalConfig ({
            frameId: "oodsp-selector-frame",
            title: event.target.dataset.title || "",
            width: "100%",
            height: "100%",
            mode: mode,
            selectorType: "roomsOnly",
            theme: "Base",
            locale: _oodsp.locale,
            events: {
                onSelectCallback: onSelectCallback,
                onCloseCallback: onCloseCallback,
            }
        })

        setOpen( true );
    }

    const closeModal = (event) => {
        if(event._reactName != "onBlur") {
            setOpen( false );
            if (DocSpace) {
                DocSpace.SDK.frames["oodsp-selector-frame"].destroyFrame();
            }
        }
    }

    if (attributes.hasOwnProperty('width') && attributes.width.length > 0) {
        blockStyle.width = attributes.width;
    }

    if (attributes.hasOwnProperty('height') && attributes.height.length > 0) {
        blockStyle.height = attributes.height;
    }

    let showWidthControl = true;

    if (attributes.align === "full") {
        delete blockStyle.width;
        showWidthControl = false;
    }

    let showPlaceholder = ! attributes.roomId && ! attributes.fileId;
    let entityType = ! showPlaceholder && attributes.roomId ? "room" : "file";
    let entityLabel = ! showPlaceholder && attributes.roomId ? __("Room", "onlyoffice-docspace-plugin") : __("File", "onlyoffice-docspace-plugin");
    let entityIcon = getIconByType(entityType);
    let entytiIsPublic = attributes.hasOwnProperty('requestToken') && attributes.requestToken.length > 0 ? getPublicIconByType(entityType) : "";

    const blockProps = showPlaceholder ?  useBlockProps( { style: null } ) : useBlockProps( { style: blockStyle } );
    return (
        <div {...blockProps}>
            {! showPlaceholder ?
                <>
                    <InspectorControls key="setting">
                        <PanelBody title={ __("Settings", "onlyoffice-docspace-plugin") }>
                            {       
                                showWidthControl ?
                                    <HeightControl label={ __("Width", "onlyoffice-docspace-plugin") } value={attributes.width} onChange={ ( value ) => setAttributes({ width: value }) }/>
                                    :
                                    ''
                            }
                            <HeightControl label={ __("Height", "onlyoffice-docspace-plugin") } value={attributes.height} onChange={ ( value ) => setAttributes({ height: value }) }/>
                            <SelectControl
                                label={__("Theme", "onlyoffice-docspace-plugin")}
                                value={attributes.theme}
                                options={themes}
                                onChange={(value) => {setAttributes({ theme: value })}}
                            />
                            { 
                                attributes.fileId ?
                                    <SelectControl
                                        label={__("View", "onlyoffice-docspace-plugin")}
                                        value={attributes.editorType}
                                        options={editorTypes}
                                        onChange={(value) => {setAttributes({ editorType: value })}}
                                    />
                                    :
                                    ''
                            }
                        </PanelBody>
                    </InspectorControls>

                    <div className={ `wp-block-onlyoffice-docspace-wordpress-onlyoffice-docspace__editor ${entityType}`}>
                        <tbody>
                            <tr>
                                <td valign="middle">
                                    <div class="entity-icon">
                                        {
                                            attributes.icon && !showDefaultIcon ? 
                                                <img src={ wp.oodsp.getAbsoluteUrl(attributes.icon) } onError={() => { setShowDefaultIcon( true ) }} />
                                                :
                                                <>{entityIcon}</>
                                        }
                                    </div>
                                </td>
                                <td class="entity-info">
                                    <p class="entity-info-label">DocSpace {entityLabel} {entytiIsPublic}</p>
                                    <p><span style={{fontWeight: 500}}>{__("Name")}:</span> {attributes.name || ""}</p>
                                </td>
                            </tr>
                        </tbody>
                    </div>

                    <BlockControls>
                        <ToolbarGroup>
                            <Dropdown
                                popoverProps={{ variant: 'toolbar' }}
                                renderToggle={ ( { isOpenDropdown, onToggle } ) => (
                                    <ToolbarButton
                                        aria-expanded={ isOpenDropdown }
                                        aria-haspopup="true"
                                        onClick={ onToggle }
                                    >
                                        { __("Replace", "onlyoffice-docspace-plugin") }
                                    </ToolbarButton>
                                ) }
                                renderContent={ ( { onClose } ) => (
                                    <>
                                        <NavigableMenu>
                                            <MenuItem
                                                onClick={ (event) => {
                                                    event.target.dataset.title=__("Select room", "onlyoffice-docspace-plugin");
                                                    event.target.dataset.mode="room-selector";
                                                    openModal(event);
                                                    onClose(); 
                                                }}
                                            >
                                                { __("Room", "onlyoffice-docspace-plugin") }
                                            </MenuItem>
                                            <MenuItem
                                                onClick={ (event) => {
                                                    event.target.dataset.title=__("Select file", "onlyoffice-docspace-plugin");
                                                    event.target.dataset.mode="file-selector";
                                                    openModal(event);
                                                    onClose(); 
                                                }}
                                            >
                                                { __("File", "onlyoffice-docspace-plugin") }
                                            </MenuItem>
                                        </NavigableMenu>
                                    </>
                                ) }
                            />
                        </ToolbarGroup>
                    </BlockControls>
                </>
            :
                <>
                    <Placeholder
                        icon={onlyofficeIcon} 
                        label="ONLYOFFICE DocSpace"
                        instructions={ __("Pick room or media file from your DocSpace", "onlyoffice-docspace-plugin") }
                        >
                        <Button
                            variant="primary"
                            data-title={ __("Select room", "onlyoffice-docspace-plugin") }
                            data-mode="room-selector"
                            onClick={ openModal }
                        >
                            { __("Select room", "onlyoffice-docspace-plugin") }
                        </Button>
                        <Button
                            variant="primary"
                            data-title={ __("Select file", "onlyoffice-docspace-plugin") }
                            data-mode="file-selector"
                            onClick={ openModal }
                            >
                            { __("Select file", "onlyoffice-docspace-plugin") }
                        </Button>
                    </Placeholder>
                </>
            }
            { isOpen && (
                <Modal onRequestClose={ closeModal } title={ modalConfig.title } >
                    <div class="oodsp-selector-frame-modal">
                        <div id="oodsp-selector-frame"></div>
                    </div>
                </Modal>
            ) }
        </div>
    );
};

export default Edit;