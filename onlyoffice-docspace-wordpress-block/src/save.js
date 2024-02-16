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

import { RawHTML } from '@wordpress/element';

const Save = ({ attributes }) => {
    if ( !attributes.roomId && !attributes.fileId ) {
        return '';
    }

    let parameters = '';

    if ( attributes.hasOwnProperty('roomId')) {
        parameters += 'roomId=' + attributes.roomId + ' ';
    } else if (attributes.hasOwnProperty('fileId')) {
        parameters += 'fileId=' + attributes.fileId + ' ';
    }

    if ( attributes.hasOwnProperty('width') && attributes.width.length > 0 ) {
        parameters += 'width=' + attributes.width + ' ';
    }

    if ( attributes.hasOwnProperty('height') && attributes.height.length > 0 ) {
        parameters += 'height=' + attributes.height + ' ';
    }

    if ( attributes.hasOwnProperty('align') && attributes.align.length > 0 ) {
        parameters += 'align=' + attributes.align + ' ';
    }

    if ( attributes.hasOwnProperty('theme') && attributes.theme.length > 0 ) {
        parameters += 'theme=' + attributes.theme + ' ';
    }

    if ( attributes.hasOwnProperty('requestToken') && attributes.requestToken.length > 0 ) {
        parameters += 'requestToken=' + attributes.requestToken + ' ';
    }

    return <RawHTML>{ `[onlyoffice-docspace ${ parameters } /]` }</RawHTML>;
};
export default Save;
