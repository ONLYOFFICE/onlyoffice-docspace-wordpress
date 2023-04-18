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

import { RawHTML } from '@wordpress/element';

const Save = ({ attributes }) => {
    if ( !attributes.fileId ) {
        return '';
    }

    let parameters = '';

    if ( attributes.hasOwnProperty('width') ) {
        parameters += 'width=' + attributes.width + ' ';
    }

    if ( attributes.hasOwnProperty('height') ) {
        parameters += 'height=' + attributes.height + ' ';
    }

    if ( attributes.hasOwnProperty('showHeader') ) {
        parameters += 'showHeader=' + attributes.showHeader + ' ';
    }

    if ( attributes.hasOwnProperty('showTitle') ) {
        parameters += 'showTitle=' + attributes.showTitle + ' ';
    }

    if ( attributes.hasOwnProperty('showMenu') ) {
        parameters += 'showMenu=' + attributes.showMenu + ' ';
    }

    if ( attributes.hasOwnProperty('showFilter') ) {
        parameters += 'showFilter=' + attributes.showFilter + ' ';
    }

    if ( attributes.hasOwnProperty('showAction') ) {
        parameters += 'showAction=' + attributes.showAction + ' ';
    }

    return <RawHTML>{ `[onlyoffice-docspace fileId=${ attributes.fileId } ${ parameters } /]` }</RawHTML>;
};
export default Save;
