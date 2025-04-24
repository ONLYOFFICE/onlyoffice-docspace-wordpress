/*
 * (c) Copyright Ascensio System SIA 2025
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

/* global oodsp, _oodspMain, DocSpace */

import {
	useBlockProps,
	InspectorControls,
	HeightControl,
	BlockControls,
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
	SelectControl,
	ColorPicker,
	FlexItem,
	Flex,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { onlyofficeIcon } from './index';
import { __ } from '@wordpress/i18n';
import { getIconByType, getPublicIconByType } from './icons';

const Edit = ( { attributes, setAttributes } ) => {
	const [ isOpen, setOpen ] = useState( false );
	const [ modalTitle, setModalTitle ] = useState();
	const [ modalConfig, setModalConfig ] = useState( {} );
	const [ showDefaultIcon, setShowDefaultIcon ] = useState( false );

	const blockStyle = {
		height: '500px',
		background: '#a2ccef',
	};

	const themes = [
		{
			label: __( 'Light', 'onlyoffice-docspace-plugin' ),
			value: 'Base',
		},
		{
			label: __( 'Dark', 'onlyoffice-docspace-plugin' ),
			value: 'Dark',
		},
	];

	const editorTypes = [
		{
			label: __( 'Embedded', 'onlyoffice-docspace-plugin' ),
			value: 'embedded',
		},
		{
			label: __( 'Editor', 'onlyoffice-docspace-plugin' ),
			value: 'desktop',
		},
	];

	const script = () => {
		if ( isOpen ) {
			oodsp.main.loadDocspace( 'oodsp-selector-frame', function () {
				DocSpace.SDK.initFrame(
					Object.assign( modalConfig, { src: DocSpace.SDK.src } )
				);
			} );
		}
	};

	useEffect( script, [ isOpen, modalConfig ] );

	const onSelectRoomCallback = ( event ) => {
		Object.keys( attributes ).forEach( ( key ) => {
			if (
				[
					'roomId',
					'fileId',
					'name',
					'icon',
					'requestToken',
					'editorType',
				].includes( key )
			) {
				delete attributes[ key ];
			}
		} );

		const requestTokens = event[ 0 ].requestTokens;
		const requestToken = requestTokens
			? requestTokens[ 0 ].requestToken
			: null;

		setAttributes( {
			roomId: new String( event[ 0 ].id ),
			name: event[ 0 ].label,
			icon: event[ 0 ].icon,
		} );

		if ( requestToken ) {
			setAttributes( {
				requestToken,
			} );
		}

		DocSpace.SDK.frames[ 'oodsp-selector-frame' ].destroyFrame();
		setOpen( false );
	};

	const onSelectFileCallback = ( event ) => {
		Object.keys( attributes ).forEach( ( key ) => {
			if (
				[
					'roomId',
					'fileId',
					'name',
					'icon',
					'requestToken',
					'documentType',
				].includes( key )
			) {
				delete attributes[ key ];
			}
		} );

		const requestTokens = event.requestTokens;
		const requestToken = requestTokens
			? requestTokens[ 0 ].requestToken
			: null;

		setAttributes( {
			fileId: new String( event.id ),
			name: event.title,
			icon: event.icon,
			documentType: event.documentType,
		} );

		if ( requestToken ) {
			setAttributes( {
				requestToken,
			} );
		}

		DocSpace.SDK.frames[ 'oodsp-selector-frame' ].destroyFrame();
		setOpen( false );
	};

	const onCloseCallback = () => {
		DocSpace.SDK.frames[ 'oodsp-selector-frame' ].destroyFrame();
		setOpen( false );
	};

	const openModal = ( event ) => {
		const mode = event.target.dataset.mode || null;
		let onSelectCallback = null;

		switch ( mode ) {
			case 'room-selector':
				onSelectCallback = onSelectRoomCallback;
				break;
			case 'file-selector':
				onSelectCallback = onSelectFileCallback;
				break;
		}

		setModalTitle(
			__( 'ONLYOFFICE DocSpace', 'onlyoffice-docspace-plugin' )
		);
		setModalConfig( {
			frameId: 'oodsp-selector-frame',
			width: '100%',
			height: '100%',
			mode,
			selectorType: 'roomsOnly',
			theme: 'Base',
			locale: _oodspMain.locale,
			events: {
				onAppReady: () => {
					setModalTitle( event.target.dataset.title || '' );
				},
				onSelectCallback,
				onCloseCallback,
			},
		} );

		setOpen( true );
	};

	const closeModal = () => {
		setOpen( false );
		if ( DocSpace ) {
			DocSpace.SDK.frames[ 'oodsp-selector-frame' ].destroyFrame();
		}
	};

	const getAbsoluteUrl = ( relativeUrl ) => {
		const docSpaceUrl = _oodspMain.docspaceUrl.endsWith( '/' )
			? _oodspMain.docspaceUrl.slice( 0, -1 )
			: _oodspMain.docspaceUrl;
		let url;

		if ( ! docSpaceUrl || docSpaceUrl === '' ) {
			return relativeUrl;
		}

		if (
			relativeUrl.startsWith( 'http://' ) ||
			relativeUrl.startsWith( 'https://' )
		) {
			const originRelativeUrl = new URL( relativeUrl ).origin;
			url = new URL(
				relativeUrl.replace( originRelativeUrl, docSpaceUrl )
			);
		} else {
			url = new URL( docSpaceUrl );
			url.pathname = relativeUrl;
		}

		return url.toString();
	};

	if ( attributes.hasOwnProperty( 'width' ) && attributes.width.length > 0 ) {
		blockStyle.width = attributes.width;
	}

	if (
		attributes.hasOwnProperty( 'height' ) &&
		attributes.height.length > 0
	) {
		blockStyle.height = attributes.height;
	}

	let showWidthControl = true;

	if ( attributes.align === 'full' ) {
		delete blockStyle.width;
		showWidthControl = false;
	}

	const showPlaceholder = ! attributes.roomId && ! attributes.fileId;
	const entityType = ! showPlaceholder && attributes.roomId ? 'room' : 'file';
	const entityLabel =
		! showPlaceholder && attributes.roomId
			? __( 'Room', 'onlyoffice-docspace-plugin' )
			: __( 'File', 'onlyoffice-docspace-plugin' );
	const entityIcon = getIconByType( entityType );
	const entytiIsPublic =
		attributes.hasOwnProperty( 'requestToken' ) &&
		attributes.requestToken.length > 0
			? getPublicIconByType( entityType )
			: '';

	const blockStyles = showPlaceholder ? null : blockStyle;
	const blockProps = useBlockProps( { style: blockStyles } );
	return (
		<div { ...blockProps }>
			{ ! showPlaceholder ? (
				<>
					<InspectorControls key="setting">
						<PanelBody
							title={ __(
								'Settings',
								'onlyoffice-docspace-plugin'
							) }
						>
							{ showWidthControl ? (
								<HeightControl
									label={ __(
										'Width',
										'onlyoffice-docspace-plugin'
									) }
									value={ attributes.width }
									onChange={ ( value ) =>
										setAttributes( { width: value } )
									}
								/>
							) : (
								''
							) }
							<HeightControl
								label={ __(
									'Height',
									'onlyoffice-docspace-plugin'
								) }
								value={ attributes.height }
								onChange={ ( value ) =>
									setAttributes( { height: value } )
								}
							/>
							<SelectControl
								label={ __(
									'Theme',
									'onlyoffice-docspace-plugin'
								) }
								value={ attributes.theme }
								options={ themes }
								onChange={ ( value ) => {
									setAttributes( { theme: value } );
								} }
							/>
							{ attributes.fileId ? (
								<>
									<SelectControl
										label={ __(
											'View',
											'onlyoffice-docspace-plugin'
										) }
										value={ attributes.editorType }
										options={ editorTypes }
										onChange={ ( value ) => {
											setAttributes( {
												editorType: value,
											} );
										} }
									/>
									{ attributes.documentType === 'slide' ? (
										<div>
											<Flex
												direction="column"
												style={ { height: 'unset' } }
											>
												<FlexItem>
													<label
														htmlFor="slidePlayerBackground"
														style={ {
															fontSize: '11px',
															fontWeight: '500',
															lineHeight: '1.4',
															textTransform:
																'uppercase',
															boxSizing:
																'border-box',
															display: 'block',
														} }
													>
														{ __(
															'Background color',
															'onlyoffice-docspace-plugin'
														) }
													</label>
												</FlexItem>
												<FlexItem>
													<ColorPicker
														id="slidePlayerBackground"
														defaultValue={
															attributes.slidePlayerBackground ||
															'#000000'
														}
														onChange={ (
															color
														) => {
															setAttributes( {
																slidePlayerBackground:
																	color,
															} );
														} }
													/>
												</FlexItem>
											</Flex>
										</div>
									) : (
										''
									) }
								</>
							) : (
								''
							) }
						</PanelBody>
					</InspectorControls>

					<div
						className={ `wp-block-onlyoffice-docspace-wordpress-onlyoffice-docspace__editor ${ entityType }` }
					>
						<tbody>
							<tr>
								<td valign="middle">
									<div className="entity-icon">
										{ attributes.icon &&
										! showDefaultIcon ? (
											<img
												alt=""
												src={ getAbsoluteUrl(
													attributes.icon
												) }
												onError={ () => {
													setShowDefaultIcon( true );
												} }
											/>
										) : (
											<>{ entityIcon }</>
										) }
									</div>
								</td>
								<td className="entity-info">
									<p className="entity-info-label">
										DocSpace { entityLabel }{ ' ' }
										{ entytiIsPublic }
									</p>
									<p>
										<span style={ { fontWeight: 500 } }>
											{ __( 'Name' ) }:
										</span>{ ' ' }
										{ attributes.name || '' }
									</p>
								</td>
							</tr>
						</tbody>
					</div>

					<BlockControls>
						<ToolbarGroup>
							<Dropdown
								popoverProps={ { variant: 'toolbar' } }
								renderToggle={ ( {
									isOpenDropdown,
									onToggle,
								} ) => (
									<ToolbarButton
										aria-expanded={ isOpenDropdown }
										aria-haspopup="true"
										onClick={ onToggle }
									>
										{ __(
											'Replace',
											'onlyoffice-docspace-plugin'
										) }
									</ToolbarButton>
								) }
								renderContent={ ( { onClose } ) => (
									<>
										<NavigableMenu>
											<MenuItem
												onClick={ ( event ) => {
													event.target.dataset.title =
														__(
															'Select room',
															'onlyoffice-docspace-plugin'
														);
													event.target.dataset.mode =
														'room-selector';
													openModal( event );
													onClose();
												} }
											>
												{ __(
													'Room',
													'onlyoffice-docspace-plugin'
												) }
											</MenuItem>
											<MenuItem
												onClick={ ( event ) => {
													event.target.dataset.title =
														__(
															'Select file',
															'onlyoffice-docspace-plugin'
														);
													event.target.dataset.mode =
														'file-selector';
													openModal( event );
													onClose();
												} }
											>
												{ __(
													'File',
													'onlyoffice-docspace-plugin'
												) }
											</MenuItem>
										</NavigableMenu>
									</>
								) }
							/>
						</ToolbarGroup>
					</BlockControls>
				</>
			) : (
				<>
					<Placeholder
						icon={ onlyofficeIcon }
						label="ONLYOFFICE DocSpace"
						instructions={ __(
							'Pick room or media file from your DocSpace',
							'onlyoffice-docspace-plugin'
						) }
					>
						<Button
							variant="primary"
							data-title={ __(
								'Select room',
								'onlyoffice-docspace-plugin'
							) }
							data-mode="room-selector"
							onClick={ openModal }
						>
							{ __(
								'Select room',
								'onlyoffice-docspace-plugin'
							) }
						</Button>
						<Button
							variant="primary"
							data-title={ __(
								'Select file',
								'onlyoffice-docspace-plugin'
							) }
							data-mode="file-selector"
							onClick={ openModal }
						>
							{ __(
								'Select file',
								'onlyoffice-docspace-plugin'
							) }
						</Button>
					</Placeholder>
				</>
			) }
			{ isOpen && (
				<Modal
					title={ modalTitle }
					onRequestClose={ closeModal }
					focusOnMount={ false }
				>
					<div className="oodsp-selector-frame-modal">
						<div id="oodsp-selector-frame"></div>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default Edit;
