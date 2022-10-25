import { Fragment, Component } from "@wordpress/element";
import AceEditor from "react-ace";
import "ace-builds/src-noconflict/mode-twig";
import "ace-builds/src-noconflict/mode-scss";
import "ace-builds/src-noconflict/mode-javascript";
import "ace-builds/src-noconflict/theme-textmate";
import "ace-builds/src-noconflict/ext-language_tools";
import "ace-builds/src-noconflict/ext-beautify";
import "ace-builds/src-noconflict/snippets/html";
import "ace-builds/src-noconflict/snippets/twig";
import "ace-builds/src-noconflict/snippets/javascript";
import "ace-builds/src-noconflict/ext-emmet";
import 'emmet-core';
import Modal from "react-modal";
import {sprintf} from "@wordpress/i18n";

export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            value : this.props.value,
            modalActive : false
        };
    }
    _update = ( value ) =>
    {
        this.setState( { value : value }, () =>
            {
                this.props.onChange( value );
            }
        );
    }
    openModal = () => {
        this.setState({modalActive: true});
    }
    closeModal = () => {
        this.setState({modalActive: false});
    }
    render()
    {
        return (
            <Fragment>
                <div className="code-input-container">
                <button className="devkit-expand-contract expand" onClick={this.openModal}><span className="dashicons dashicons-fullscreen-alt"></span></button>
                <AceEditor
                    mode={this.props.mode}
                    theme="textmate"
                    onChange={this._update}
                    width="100%"
                    minLines={this.props.minLines || 10}
                    maxLines={this.props.maxLines || 200}
                    value={this.state.value}
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
                <Modal
                  isOpen={this.state.modalActive}
                  // onAfterOpen={afterOpenModal}
                  onRequestClose={this.closeModal}
                  ariaHideApp={false}
                  contentLabel="Example Modal"
                  overlayClassName="devkit_modal"
                >
                  <header>
                      <button className="devkit-expand-contract expand" onClick={this.closeModal}><span className="dashicons dashicons-fullscreen-exit-alt"></span></button>
                  </header>
                    {/*dashicons-editor-contract*/}
                    <div className={"modal-body"}>
                        <AceEditor
                            mode={this.props.mode}
                            theme="textmate"
                            onChange={this._update}
                            width="100%"
                            height={"calc(100vh - 80px)"}
                            value={this.state.value}
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
                    </div>
                </Modal>
                <input
                    type="hidden"
                    name={this.props.name}
                    value={this.state.value}
                />
                </div>
            </Fragment>
        )
    }
}