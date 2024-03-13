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


const roomIcon = (
  <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="2" y="2" width="92" height="92" rx="10" stroke="black" stroke-opacity="0.17" stroke-width="4"/>
    <rect x="18" y="18" width="25" height="25" rx="3" fill="white"/>
    <rect x="18" y="54" width="25" height="25" rx="3" fill="white"/>
    <rect x="53" y="54" width="25" height="25" rx="3" fill="white"/>
    <rect x="53" y="18" width="25" height="25" rx="3" fill="white"/>
  </svg>
);
 
const fileIcon = (
  <svg width="97" height="96" viewBox="0 0 97 96" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M10.5 1H57.5L86.5 30V95H10.5V1Z" fill="white"/>
    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5 95H86.5V30L57.5 1H10.5V95ZM57.5 0H9.5V96H87.5V30L57.5 0Z" fill="url(#paint0_linear_3527_59860)"/>
    <path d="M33.5 67H39.5V73H33.5V67Z" fill="#BFBFBF"/>
    <path d="M45.5 67H51.5V73H45.5V67Z" fill="#BFBFBF"/>
    <path d="M63.5 67H57.5V73H63.5V67Z" fill="#BFBFBF"/>
    <path opacity="0.3" d="M56.5 30V1H57.5V29H86.5L87.5 30H56.5Z" fill="black"/>
    <defs>
    <linearGradient id="paint0_linear_3527_59860" x1="48.5" y1="94.25" x2="48.5" y2="1.21299e-06" gradientUnits="userSpaceOnUse">
    <stop stop-color="#A8A8A8"/>
    <stop offset="1" stop-color="#DADADA"/>
    </linearGradient>
    </defs>
  </svg>
);

const publicRoomIcon = (
  <svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M8.5 10.4197V4.58032C8.5 4.22147 8.69229 3.89012 9.00386 3.71208L14.0039 0.854937C14.6705 0.47399 15.5 0.955357 15.5 1.72318V13.2768C15.5 14.0446 14.6705 14.526 14.0039 14.1451L9.00386 11.2879C8.69229 11.1099 8.5 10.7785 8.5 10.4197Z" fill="#333333"/>
    <rect x="1.5" y="4" width="6" height="7" rx="1" fill="#333333"/>
    <path d="M4.38184 12L5.60542 14.4472C5.85241 14.9411 6.45308 15.1414 6.94706 14.8944C7.44104 14.6474 7.64126 14.0467 7.39427 13.5527L6.6179 12H4.38184Z" fill="#333333"/>
  </svg>
);

const publicFileIcon = (
  <svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M8.5 10.4197V4.58032C8.5 4.22147 8.69229 3.89012 9.00386 3.71208L14.0039 0.854937C14.6705 0.47399 15.5 0.955357 15.5 1.72318V13.2768C15.5 14.0446 14.6705 14.526 14.0039 14.1451L9.00386 11.2879C8.69229 11.1099 8.5 10.7785 8.5 10.4197Z" fill="white"/>
    <rect x="1.5" y="4" width="6" height="7" rx="1" fill="white"/>
    <path d="M4.38184 12L5.60542 14.4472C5.85241 14.9411 6.45308 15.1414 6.94706 14.8944C7.44104 14.6474 7.64126 14.0467 7.39427 13.5527L6.6179 12H4.38184Z" fill="white"/>
  </svg>
);

export const getIconByType = (type) => {
  switch(type) {
    case "room":
      return roomIcon;
    case "file":
      return fileIcon;
    default:
      return null;
  }
};

export const getPublicIconByType = (type) => {
  switch(type) {
    case "room":
      return publicRoomIcon;
    case "file":
      return publicFileIcon;
    default:
      return null;
  }
};

