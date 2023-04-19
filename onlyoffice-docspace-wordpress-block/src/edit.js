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
    CheckboxControl,
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

const Edit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps({ style: blockStyle });
    const [isOpen, setOpen] = useState( false );
    const [modalConfig, setModalConfig] = useState( {} );
    const [showDefaultIcon, setShowDefaultIcon] = useState( false );

    const script = () => {
        DocSpaceComponent.initScript().then(function() {
            if (isOpen) {
                console.log(modalConfig.docspaceConfig);
                DocSpace.initFrame(modalConfig.docspaceConfig);
            }
        });
    };

    useEffect(script, [isOpen]);

    const onSelectCallback = (event) => {
        setAttributes({ fileId: event[0].id, fileName: event[0].label, icon: event[0].icon });
        setOpen(false);
    }

    const onCloseCallback = () => {
        setOpen(false);
    }

    const openModal = (event) => {
        var docspaceConfig = {
            "frameId": "ds-frame-select",
            "width": "400px",
            "height": "500px",
            "mode": event.target.dataset.mode || null,
            "events": {
                "onSelectCallback": onSelectCallback,
                "onCloseCallback": onCloseCallback
            }
        };

        setModalConfig ({
            title: event.target.dataset.title || "",
            docspaceConfig: docspaceConfig
        })

        setOpen( true );
    }

    const closeModal = (event) => {
        if(event._reactName != "onBlur") {
            setOpen( false );
            setAttributes({ fileId: 1 });
        }
    }

    return (
        <div {...blockProps}>
            {attributes.fileId ?
                <div>
                    <InspectorControls key="setting">
                        <PanelBody title={ 'Settings' }>
                            <HeightControl label={ 'Width' } value={attributes.width} onChange={ ( value ) => setAttributes({ width: value }) }/>
                            <HeightControl label={ 'Height' } value={attributes.height} onChange={ ( value ) => setAttributes({ height: value }) }/>
                            <CheckboxControl label={ 'Left menu' } checked={attributes.showMenu} onChange={ ( value ) => setAttributes({ showMenu: value }) } />
                            <CheckboxControl label={ 'Navigation and Title' } checked={attributes.showTitle} onChange={ ( value ) => setAttributes({ showTitle: value }) } />
                            <CheckboxControl label={ 'Action button' } checked={attributes.showAction} onChange={ ( value ) => setAttributes({ showAction: value }) } />
                            <CheckboxControl label={ 'Search, Filter and Sort' } checked={attributes.showFilter} onChange={ ( value ) => setAttributes({ showFilter: value }) } />
                            <CheckboxControl label={ 'Header' } checked={attributes.showHeader} onChange={ ( value ) => setAttributes({ showHeader: value }) } />
                        </PanelBody>
                    </InspectorControls>
                    <p style={{display: 'flex'}}>
                    {attributes.icon && !showDefaultIcon ? 
                        <img class='docspace-icon' src={ attributes.icon }  onerror={() => { console.log("tatat"); setShowDefaultIcon( true )}} />
                        :
                        <div>{onlyofficeIcon}</div>
                    }
                    <p style={{marginLeft: '25px'}}> {attributes.fileName || ""}</p>
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
                                        { 'Replace' }
                                    </ToolbarButton>
                                ) }
                                renderContent={ ( { onClose } ) => (
                                    <>
                                        <NavigableMenu>
                                            <MenuItem
                                                onClick={ (event) => {
                                                    event.target.dataset.title="Select room";
                                                    event.target.dataset.mode="room selector";
                                                    openModal(event);
                                                    onClose(); 
                                                }}
                                            >
                                                { 'Room' }
                                            </MenuItem>
                                            <MenuItem
                                                onClick={ (event) => {
                                                    event.target.dataset.title="Select file";
                                                    event.target.dataset.mode="manager";
                                                    openModal(event);
                                                    onClose(); 
                                                }}
                                            >
                                                { 'File' }
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
                        instructions="Pick room or media file from your DocSpace "
                        >
                        <Button
                            variant="primary"
                            data-title="Select room"
                            data-mode="room selector"
                            onClick={ openModal }
                        >
                            { 'Select room' }
                        </Button>
                        <Button
                            variant="primary"
                            data-title="Select file"
                            data-mode="manager"
                            onClick={ openModal }
                            >
                            { 'Select file' }
                        </Button>
                    </Placeholder>
                </div>
            }
            { isOpen && (
                <Modal onRequestClose={ closeModal } title={ modalConfig.title }>
                    <div id="ds-frame-select">Fallback text</div>
                </Modal>
            ) }
        </div>
    );
};

export default Edit;