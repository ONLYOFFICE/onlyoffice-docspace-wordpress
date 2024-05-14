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
import block from '../block.json';

const Save = ({ attributes }) => {
    if ( !attributes.roomId && !attributes.fileId ) {
        return '';
    }

    let parameters = '';

    for( var key in Object.keys( block.attributes ) ) {
        if ( ! _isEmpty(attributes[key]) ) {
            parameters += key + '=' + attributes[key] + ' ';
        }
    }

    return <RawHTML>{ `[onlyoffice-docspace ${ parameters } /]` }</RawHTML>;
};

const _isEmpty = ( value ) => {
    return ( value == null ||  ( typeof value === "string" && value.trim().length === 0 ) );
}

export default Save;
