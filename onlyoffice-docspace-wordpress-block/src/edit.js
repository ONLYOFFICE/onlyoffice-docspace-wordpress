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

import { useBlockProps } from '@wordpress/block-editor';
import { Button, Placeholder, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { blockStyle, onlyofficeIcon } from "./index";

const Edit = ({ attributes, setAttributes }) => {

    const blockProps = useBlockProps({ style: blockStyle });

    if (!attributes.frameId) {
        setAttributes({ frameId: DocSpaceComponent.generateId() });
    }

    if (!attributes.frameConfig && attributes.frameId) {
        setAttributes({
            frameConfig: {
                "height": "400px",
                "frameId": "ds-frame-" + attributes.frameId,
                "mode": "manager",
                "showHeader": false,
                "showTitle": true,
                "showMenu": false,
                "showFilter": false,
                "showAction": false,
            }
        });
    }

    var init = false;

    const [isOpen, setOpen] = useState( false );
    const [title, setTitle] = useState( "" );
    const openModal = (e) => {
        console.log(e.target.id);
        switch (e.target.id) {
            case ("selectFile"):
                setTitle( "Select file" );
                break;
            case ("selectRoom"):
                setTitle( "Select room" );
                break;
            default:
                setTitle( "Select file" );
        }

        setOpen( true );

        DocSpaceComponent.initScript().then(function () {
            if (!window.DocSpace || init) return;
            init = true;
            DocSpace.initFrame(attributes.frameConfig)
        });
    }
    const closeModal = (e) => {
        if(e._reactName != "onBlur") {
            setOpen( false );
        }
    }
    return (
        <div {...blockProps}>
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
                    <div id={"ds-frame-" + attributes.frameId}>Fallback text</div>
                </Modal>
            ) }
        </div>
    );
};

export default Edit;