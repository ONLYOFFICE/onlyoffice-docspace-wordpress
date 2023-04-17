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

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { CheckboxControl, Button, Placeholder, Modal,    PanelBody,
    __experimentalInputControl as InputControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { blockStyle, onlyofficeIcon } from "./index";

const Edit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps({ style: blockStyle });
    const [isOpen, setOpen] = useState( false );
    const [title, setTitle] = useState( "" );

    useEffect(DocSpaceComponent.initScript, []);

    const openModal = (e) => {
        var config = {
            "frameId": "ds-frame-select",
            "height": "400px",
            "mode": "viewer"
        };

        switch (e.target.id) {
            case ("selectFile"):
                setTitle( "Select file" );
                config.mode = "viewer";
                break;
            case ("selectRoom"):
                setTitle( "Select room" );
                config.mode = "manager";
                break;
            default:
                setTitle( "Select file" );
        }

        DocSpaceComponent.initScript().then(function() {
            DocSpace.initFrame(config);
        });

        setOpen( true );
    }

    const closeModal = (e) => {
        if(e._reactName != "onBlur") {
            DocSpace.destroyFrame();
            setOpen( false );
        }
    }

    return (
        <div {...blockProps}>
            <InspectorControls key="setting">
                    <PanelBody title={ 'Settings' }>
                        <InputControl label={' Width' } value={attributes.width} onChange={ ( value ) => setAttributes({ width: value }) } />
                        <InputControl label={ 'Height' } value={attributes.height} onChange={ ( value ) => setAttributes({ height: value }) } />
                        <InputControl label={ 'src' } value={attributes.src} onChange={ ( value ) => setAttributes({ src: value }) } />
                        <InputControl label={ 'rootPath' } value={attributes.rootPath} onChange={ ( value ) => setAttributes({ rootPath: value }) } />
                        <InputControl label={ 'mode' } value={attributes.mode} onChange={ ( value ) => setAttributes({ mode: value }) } />
                        <CheckboxControl label={ 'showHeader' } checked={attributes.showHeader} onChange={ ( value ) => setAttributes({ showHeader: value }) } />
                        <CheckboxControl label={ 'showTitle' } checked={attributes.showTitle} onChange={ ( value ) => setAttributes({ showTitle: value }) } />
                        <CheckboxControl label={ 'showMenu' } checked={attributes.showMenu} onChange={ ( value ) => setAttributes({ showMenu: value }) } />
                        <CheckboxControl label={ 'showFilter' } checked={attributes.showFilter} onChange={ ( value ) => setAttributes({ showFilter: value }) } />
                        <CheckboxControl label={ 'showAction' } checked={attributes.showAction} onChange={ ( value ) => setAttributes({ showAction: value }) } />
                    </PanelBody>
            </InspectorControls>
            <Placeholder
                icon={onlyofficeIcon} 
                label="ONLYOFFICE DocSpace"
                instructions="Pick room or media file from your DocSpace "
                >
                <Button
                    id="selectRoom"
                    variant="primary"
                    onClick={ openModal }
                >
                    { 'Select room' }
                </Button>
                <Button
                    id="selectFile"
                    variant="primary"
                    onClick={ openModal }
                    >
                    { 'Select file' }
                </Button>
            </Placeholder>
            { isOpen && (
                <Modal onRequestClose={ closeModal } title={ title }>
                    <div id="ds-frame-select">Fallback text</div>
                </Modal>
            ) }
        </div>
    );
};

export default Edit;