import { sprintf, __ } from '@wordpress/i18n';
import { Component} from "@wordpress/element";

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

class ScriptsMetabox extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
			scripts : this.props.meta.scripts.raw,
			styles : this.props.meta.styles.raw
        };
    }
    _getValue = ( needle, haystack ) =>
    {
        return this.props.fields[haystack].values[needle] || this.props.fields[haystack].default;
    }
    _update = ( value, property ) =>
    {
        this.setState( { [property] : value } );
    }
    render() {
        return (
            <fieldset>
				<div className='field-group'>
                    <div className="field">
						<label
                            htmlFor={sprintf('%s[scripts][raw]', this.props.key)}>
                            {__( 'Javascript', 'devkit_layouts' )}
                        </label>
                        <AceEditor
                            mode="javascript"
                            theme="textmate"
                            onChange={ (value) => this._update( value, 'scripts' )}
                            width="100%"
                            minLines={10}
                            maxLines={30}
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
                            name={sprintf('%s[scripts][raw]', this.props.key)}
                            readOnly={true}
                            value={this.state.scripts}
                            style={{display : 'none'}}
                        />
                    </div>
                </div>
				<div className='field-group'>
                    <div className="field">

						<label
                            htmlFor={sprintf('%s[styles][raw]', this.props.key)}>
                            {__( 'CSS', 'devkit_layouts' )}
                        </label>

                        <AceEditor
                            mode="scss"
                            theme="textmate"
                            onChange={ (value) => this._update( value, 'styles' )}
                            width="100%"
                            minLines={10}
                            maxLines={30}
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
                            name={sprintf('%s[styles][raw]', this.props.key)}
                            readOnly={true}
                            value={this.state.styles}
							style={{display : 'none'}}
                        />
                        <div className="informational">
                            <p><span className="dashicons dashicons-editor-help"></span> Supports SCSS</p>
                        </div>
                    </div>
                </div>
            </fieldset>
	    )
    }
}
export default ScriptsMetabox;