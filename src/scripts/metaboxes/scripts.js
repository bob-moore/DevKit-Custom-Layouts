
import ReactDOM from 'react-dom/client';
import domReady from '@wordpress/dom-ready';
import { __, sprintf } from '@wordpress/i18n';

import AceEditor from "react-ace";

import "ace-builds/src-noconflict/mode-javascript";
import "ace-builds/src-noconflict/mode-scss";
import "ace-builds/src-noconflict/theme-textmate";
import "ace-builds/src-noconflict/ext-language_tools";
import "ace-builds/src-noconflict/ext-beautify";
import "ace-builds/src-noconflict/snippets/scss";
import "ace-builds/src-noconflict/snippets/css";
import "ace-builds/src-noconflict/snippets/javascript";
import "ace-builds/src-noconflict/ext-emmet";
import 'emmet-core';


import fieldKey from '../includes/field-key';

class OptionsMetabox extends React.Component
{
    constructor(props)
    {
        super(props);

		this.fields = {
			scripts : devkit_metabox_data.fields.scripts,
			styles : devkit_metabox_data.fields.styles
		};

        this.state = {
			scripts : devkit_metabox_data.meta.scripts,
			styles : devkit_metabox_data.meta.styles
        };
    }
	_updateScripts = ( value ) =>
	{
		this.setState( { scripts : value } );
    }
	_updateStyles = ( value ) =>
	{
		this.setState( { styles : value } );
    }
    render() {
        return (
            <fieldset>
				<div className='field-group'>
                    <div className="field">
						<label htmlFor={fieldKey('scripts')}>{__( 'Javascript', 'devkit_layouts' )}</label>
                        <AceEditor
                            mode="javascript"
                            theme="textmate"
                            onChange={this._updateScripts}
                            width="100%"
                            height="300px"
                            value={this.state.scripts}
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
                            }}
                        />
                        <textarea
                            name={fieldKey('scripts')}
                            readOnly={true}
                            value={this.state.scripts}
							className='screen-reader-text'
                        />
                    </div>
                </div>
				<div className='field-group'>
                    <div className="field">
						<label htmlFor={fieldKey('styles')}>{__( 'CSS', 'devkit_layouts' )}</label>
                        <AceEditor
                            mode="scss"
                            theme="textmate"
                            onChange={this._updateStyles}
                            width="100%"
                            height="300px"
                            value={this.state.styles}
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
                            }}
                        />
                        <textarea
                            name={fieldKey('styles')}
                            readOnly={true}
                            value={this.state.styles}
							className='screen-reader-text'
                        />
                    </div>
                </div>
            </fieldset>
	    )
    }
}

domReady( function () {

	let metabox_container = document.getElementById('devkit-layouts-metabox-scripts');

	if ( metabox_container )
	{
		const root = ReactDOM.createRoot( metabox_container );
		root.render(
            <OptionsMetabox
                fields={
                    {
                        scripts : devkit_metabox_data.fields.scripts,
                        styles : devkit_metabox_data.fields.styles
                    }
                }
				meta={
					{
						scripts : devkit_metabox_data.meta.scripts,
                        styles : devkit_metabox_data.meta.styles
					}
				}
            />
        );
	}
} );