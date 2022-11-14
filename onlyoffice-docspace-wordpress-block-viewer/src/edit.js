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
import { useEffect } from '@wordpress/element';
import { blockStyle } from "./index";

const Edit = ({ attributes, setAttributes }) => {

    const blockProps = useBlockProps({ style: blockStyle });

    if (!attributes.frameId) {
        setAttributes({ frameId: DocSpaceComponent.generateId() });
    }

    if (!attributes.frameConfig && attributes.frameId) {
        setAttributes({
            frameConfig: {
                "height": "600px",
                "frameId": "ds-frame-" + attributes.frameId,
                "fileId": "2",
                "mode": "viewer",
                "showHeader": false,
                "showTitle": true,
                "showMenu": false,
                "showFilter": false,
                "showAction": false,
            }
        });
    }

    var init = false;

    const script = async () => {
        await DocSpaceComponent.initScript();
        if (!window.DocSpace || init) return;
        init = true;
        DocSpace.initFrame(Object.assign({}, attributes.frameConfig, { mode: "fileSelector" }));
    };

    useEffect(script);

    return (
        <div {...blockProps}>
            <div id={"ds-frame-" + attributes.frameId}>Fallback text</div>
        </div>
    );
};

export default Edit;