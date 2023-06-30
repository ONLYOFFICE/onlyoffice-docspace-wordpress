/*
 * (c) Copyright Ascensio System SIA 2022
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
    Dropdown
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { blockStyle, onlyofficeIcon } from "./index";
import { __ } from '@wordpress/i18n';

const Edit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps({ style: blockStyle });
    const [isOpen, setOpen] = useState( false );
    const [modalConfig, setModalConfig] = useState( {} );
    const [showDefaultIcon, setShowDefaultIcon] = useState( false );

    const script = () => {
        if (isOpen) {
            DocSpaceComponent.renderDocSpace(
                "oodsp-selector-frame",
                function() {
                    DocSpace.SDK.initFrame(modalConfig);
                }
            );
        }
    };

    useEffect(script, [isOpen]);

    const onSelectRoomCallback = (event) => {
        setAttributes({ roomId: event[0].id, name: event[0].label, icon: event[0].icon });
        DocSpace.SDK.frames["oodsp-selector-frame"].destroyFrame();
        setOpen(false);
    }

    const onSelectFileCallback = (event) => {
        setAttributes({ fileId: event.id, name: event.title, icon: event.icon });
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
            width: "400px",
            height: "500px",
            mode: mode,
            locale: DocSpaceComponent.locale,
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

    return (
        <div {...blockProps}>
            {attributes.roomId || attributes.fileId ?
                <div>
                    <InspectorControls key="setting">
                        <PanelBody title={ __("Settings", "onlyoffice-docspace-plugin") }>
                            <HeightControl label={ __("Width", "onlyoffice-docspace-plugin") } value={attributes.width} onChange={ ( value ) => setAttributes({ width: value }) }/>
                            <HeightControl label={ __("Height", "onlyoffice-docspace-plugin") } value={attributes.height} onChange={ ( value ) => setAttributes({ height: value }) }/>
                        </PanelBody>
                    </InspectorControls>
                    <p style={{display: 'flex'}}>
                    {attributes.icon && !showDefaultIcon ? 
                        <img class='docspace-icon' src={ DocSpaceComponent.getAbsoluteUrl(attributes.icon) }  onerror={() => setShowDefaultIcon( true ) } />
                        :
                        <div>{onlyofficeIcon}</div>
                    }
                    <p style={{marginLeft: '25px'}}> {attributes.name || ""}</p>
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
                </p>
                </div>
            :
                <div>
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
                </div>
            }
            { isOpen && (
                <Modal onRequestClose={ closeModal } title={ modalConfig.title } style={{ minHeight: "576px" }}>
                    <div id="oodsp-selector-frame"></div>
                </Modal>
            ) }
        </div>
    );
};

export default Edit;