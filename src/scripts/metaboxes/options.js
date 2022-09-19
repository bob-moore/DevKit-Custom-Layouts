import React, { useState, useEffect } from 'react';
import Select from 'react-select'
import ReactDOM from 'react-dom/client';
import domReady from '@wordpress/dom-ready';

import AceEditor from "react-ace";

import "ace-builds/src-noconflict/mode-twig";
import "ace-builds/src-noconflict/theme-textmate";
import "ace-builds/src-noconflict/ext-language_tools";
import "ace-builds/src-noconflict/ext-beautify";
import "ace-builds/src-noconflict/snippets/html";
import "ace-builds/src-noconflict/snippets/twig";
import "ace-builds/src-noconflict/snippets/javascript";
import "ace-builds/src-noconflict/ext-emmet";
import 'emmet-core';


import OptionSearch from '../includes/optionsearch.js';
import fieldKey from '../includes/field-key';

class OptionsMetabox extends React.Component
{
    constructor(props)
    {
        super(props);

        this.fields = {
            type : devkit_metabox_data.fields.type,
            container : devkit_metabox_data.fields.container,
            snippet : devkit_metabox_data.fields.snippet,
            partials : devkit_metabox_data.fields.partials,
            class : devkit_metabox_data.fields.class
        }

        this.state = {
            type : devkit_metabox_data.meta.type,
            snippet : devkit_metabox_data.meta.snippet,
            partial: devkit_metabox_data.meta.partial,
            container: devkit_metabox_data.meta.container,
            class : devkit_metabox_data.meta.class,
            prevType : devkit_metabox_data.meta.type,
            prevContainer : devkit_metabox_data.meta.container
        };
    }
    _updateSnippet = ( value ) => {
        this.setState({ snippet : value });
    }
    _updateContaner = ( select ) => {
        this.setState( { container : select.value } );
    }
    _updatePartial = ( select ) => {
        this.setState( { partial : select.value } );
    }

    _updateType = ( select ) => {
        let state = {
            type : select.value,
            prevType : this.state.type,
        };
        if ( select.value === 'snippet' )
        {
            state.container = '';
            state.prevContainer = this.state.container;
        }
        else if ( state.prevType === 'snippet' && this.state.container === '' )
        {
            state.container = this.state.prevContainer;
        }

        this._showHideEditor( select.value );

        this.setState( state );
    }
    _showHideEditor = ( type ) => {
        if ( ['partial', 'snippet'].includes( type ) )
        {
            document.body.classList.add('devkit-editor-hidden');
        }
        else {
            document.body.classList.remove('devkit-editor-hidden');
        }
    }
    _updateClass = ( value ) => {
        this.setState( { class : value } );
    }
    render() {
        return (
            <fieldset>
                <div className="field">
                    <label htmlFor={fieldKey('type')}>{this.fields.type.label}</label>
                    <Select
                        options={this.fields.type.options}
                        name={fieldKey('type')}
                        value={OptionSearch(this.fields.type.options, this.state.type)}
                        isClearable={false}
                        isSearchable={false}
                        onChange={this._updateType}
                    />
                </div>
                { this.state.type === 'snippet' &&
                    <div className='field-group'>
                        <div className="field">
                            <label htmlFor={fieldKey('snippet')}>{this.fields.snippet.label}</label>
                            <AceEditor
                                mode="twig"
                                theme="textmate"
                                onChange={this._updateSnippet}
                                width="100%"
                                value={this.state.snippet}
                                fontSize={16}
                                showPrintMargin={true}
                                showGutter={true}
                                highlightActiveLine={true}
                                setOptions={{
                                    enableBasicAutocompletion: true,
                                    enableLiveAutocompletion: true,
                                    enableSnippets: true,
                                    showInvisibles : false,
                                    displayIndentGuides : true,
                                    enableEmmet : true,
                                    wrapBehavioursEnabled : true
                                }}
                            />
                            <textarea
                                name={fieldKey('snippet')}
                                value={this.state.snippet}
                                readOnly={true}
                                className="screen-reader-text"
                            />
                        </div>
                    </div>
                }
                { this.state.type === 'partial' &&
                    <div className='field-group'>
                        <div className="field">
                            <label htmlFor={fieldKey('partial')}>{this.fields.partials.label}</label>
                            <Select
                                options={this.fields.partials.options}
                                name={fieldKey('partial')}
                                value={OptionSearch(this.fields.partials.options, this.state.partial, false)}
                                isSearchable={true}
                                isClearable={false}
                                onChange={this._updatePartial}
                            />
                        </div>
                    </div>
                }
                <div className="field-group">
                    <div className="field">
                        <label htmlFor={fieldKey('container')}>{this.fields.container.label}</label>
                        <Select
                            options={this.fields.container.options}
                            name={fieldKey('container')}
                            value={OptionSearch(this.fields.container.options, this.state.container, false)}
                            isSearchable={false}
                            isClearable={true}
                            onChange={this._updateContaner}
                        />
                    </div>
                    <div className={'field' + ( this.state.container === '' ? ' screen-reader-text' : '' ) }>
                        <label htmlFor={fieldKey('class')}>{this.fields.class.label}</label>
                        <input
                            type="text"
                            name={fieldKey('class')}
                            value={this.state.class}
                            onChange={this._updateClass}
                        />
                    </div>
                </div>
            </fieldset>
	    )
    }
}

domReady( function () {

	let metabox_container = document.getElementById('devkit-layouts-metabox-options');

	if ( metabox_container )
	{
		const root = ReactDOM.createRoot( metabox_container );
		root.render(
            <OptionsMetabox
                strings={devkit_metabox_data.strings}
                fieldkey={devkit_metabox_data.key}
                fields={
                    {
                        type : devkit_metabox_data.fields.type,
                        container : devkit_metabox_data.fields.container,
                        snippet : devkit_metabox_data.fields.snippet,
                        class : devkit_metabox_data.fields.class
                    }
                }
            />
        );
	}
} );