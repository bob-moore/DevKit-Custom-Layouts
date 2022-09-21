// import React, { useState, useEffect } from 'react';
import Select from 'react-select'
import domReady from '@wordpress/dom-ready';
import OptionSearch from '../includes/optionsearch.js';
import {CopyToClipboard} from 'react-copy-to-clipboard';
import { sprintf, __, _x, _n, _nx } from '@wordpress/i18n';
import { Fragment, Component, createElement, render } from "@wordpress/element";

// noinspection JSUnresolvedVariable
class LocationsMetabox extends Component
{
    constructor(props)
    {
        super(props);

        this.fields= devkit_metabox_data.fields;
        this.state = {
            locations : [],
            total : devkit_metabox_data.meta.locations.length
        };
        /**
         * Setup locations with option search
         */
        for( let i in devkit_metabox_data.meta.locations )
        {
            this.state.locations[i] = {
                hook : OptionSearch( this.fields.hook.options, devkit_metabox_data.meta.locations[i].hook),
                priority :  parseInt( devkit_metabox_data.meta.locations[i].priority )
            }
        }
        this.savingState = false;
    }
    removeField = (index) => {
        let locations = this.state.locations;

        if ( locations.length > 0 )
        {
            locations.splice( index, 1 );
            this.setState( { locations : locations } );
        }

    }
    addField = (event) => {
        let locations = this.state.locations;
        locations.push( { hook : '', priority : 10 } );
        this.setState( { locations : locations } );
    }

    _updateLocation = ( selection, index ) => {
        let locations = this.state.locations;
        locations[index].hook = selection;
        this.setState( { locations : locations } );
    }
    _updatePriority = ( priority, index ) => {
        let locations = this.state.locations;
        locations[index].priority = parseInt( priority );
        this.setState( { locations : locations } );
    }
    render() {
        return (
            <Fragment>
                <div className="field-group">
                    <div className="field group-controls">
                        <a href="#" className="add group-control" onClick={this.addField}>
                            {__( 'Add Location', 'devkit_layouts' )}
                        </a>
                    </div>
                </div>
                { ! this.state.locations.length &&
                    <div className="field-group">
                        <div className="field">
                            <div className="devkit-notice notice-error notice">
                                <p>{__( 'No Locations have been selected.', 'devkit_layouts' )} <a href='#' onClick={this.addField}>{__( 'Create One Now', 'devkit_layouts' )}</a></p>
                            </div>
                        </div>
                    </div>
                }
                {this.state.locations.map((item, i) => {
                    return (
                        <fieldset key={sprintf( '%s_%d_%d', item.hook, item.priority, i )} data-group-order={i} className="locations-group">
                            <div className="field hook-container">
                                <label
                                    htmlFor={sprintf( '%s[locations][%d][hook]', devkit_metabox_data.key, i )}
                                    className={ i > 0 ? 'screen-reader-text' : '' }>
                                    {this.fields.hook.label}
                                </label>
                                <Select
                                    options={this.fields.hook.options}
                                    name={sprintf( '%s[locations][%d][hook]', devkit_metabox_data.key, i )}
                                    isClearable={false}
                                    isSearchable={true}
                                    value={this.state.locations[i].hook}
                                    onChange={ ( selection ) => { this._updateLocation( selection, i ) } }
                                />
                            </div>
                            <div className="field priority-container">
                                <label
                                    htmlFor={sprintf( '%s[locations][%d][priority]', devkit_metabox_data.key, i )}
                                    className={ i > 0 ? 'screen-reader-text' : '' }>
                                    {this.fields.priority.label}
                                </label>
                                <input
                                    type="number"
                                    min="0" step="1"
                                    name={sprintf( '%s[locations][%d][priority]', devkit_metabox_data.key, i )}
                                    defaultValue={this.state.locations[i].priority}
                                    onBlur={ ( event ) => { this._updatePriority( event.target.value, i ) } }
                                />
                            </div>
                            <div className="field button-container">
                                <button className="devkit-button-icon button remove-control" onClick={() => this.removeField(i)}><span className="dashicons dashicons-remove"></span><span className="screen-reader-text">Remove</span></button>
                            </div>
                        </fieldset>
                    )
                })}
                <div className="field-group">
                    <div className="field">
                        <h3>Manual Placement</h3>
                        <p>Shortcode : <code>{sprintf( '[devkit_layout id="%s"]', 123 )}</code>
                        <CopyToClipboard
                            text={sprintf( '[devkit_layout id="%s"]', 123 )}
                            onCopy = {(event) => {console.log(event)}}
                        >
                            <span className={'copy-to-clipboard'}><a className={"copy"} href={"#"}>Copy to clipboard</a></span>
                        </CopyToClipboard>
                        </p>
                    </div>
                </div>
            </Fragment>
	    )
    }
}
domReady( function ()
{
	let metabox_container = document.getElementById('devkit-layouts-metabox-locations');
	if ( metabox_container )
	{
        render(
            createElement( LocationsMetabox ) ,
            metabox_container
        );
	}

} );