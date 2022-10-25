import { sprintf } from '@wordpress/i18n';
import {Fragment, Component, createRef} from "@wordpress/element";
import Select from '../../inputs/select';
import Context from './context';
import Label from '../../inputs/label.js';

// noinspection JSUnresolvedVariable
export default class extends Component {
    constructor(props) {
        super(props);

        for ( let i in this.props.context )
        {
            this.props.context[i].index = this._itemKey( i );
        }

        this.state = {
            context: this.props.context,
            display: this.props.display,
        };
    }
    _updateDisplay = ( display ) => {
        this.setState( {display : display} );
    }
    _addCondition = (event) => {

        event.preventDefault();

        let condition =
        {
            type: '',
            comparison: '=',
            subtype: '',
            id : '',
            deps: [],
            index : this._itemKey( this.state.context.length )
        }

        let context = this.state.context;
        context.push(condition);
        this.setState({context: context});
    }
    _removeRule = ( i ) => {
        let context = this.state.context
        context.splice(i, 1);
        this.setState({ context: context }, () => {
            this.props.onRemove(this.state.context, this.props.group);
        } );
    }
    _itemKey = (index) => {
        return btoa( this.props.group + '_' + index + '_' + Date.now() );
    }
    render() {
        return (
            <fieldset className="conditions-group">
                <div className="field-group">
                    <div className="field flex-unset">
                        <div className="field-group">
                            <Select
                                field={this.props.fields.display}
                                name={sprintf('%s[conditions][%d][display]', this.props.meta.key, this.props.group)}
                                value={this.state.display}
                                onChange={this._updateDisplay}
                            />
                            <span className="condition-label">IF</span>
                        </div>
                    </div>
                    <div className="field push-right">
                        <a href={"#"} className="add group-control" onClick={this._addCondition}>New Condition</a>
                    </div>
                </div>
                {this.state.context.map((condition, i) => {
                    return (
                        <Fragment key={condition.index}>
                            {i > 0 &&
                                <span className="context-label">And</span>
                            }
                            <div className="field-group">
                                <Context
                                    group={this.props.group}
                                    index={i}
                                    type={condition.type}
                                    comparison={condition.comparison}
                                    subtype={condition.subtype}
                                    deps={condition.deps}
                                    id={condition.id}
                                    fields={this.props.fields}
                                    meta={this.props.meta}
                                />
                                <div className="field" style={{flex : 'unset', width : '40px'}}>
                                    <button className="devkit-button-icon button remove-control" onClick={(e) => {e.preventDefault(); this._removeRule(i)}}>
                                        <span className="dashicons dashicons-remove"></span>
                                        <span className="screen-reader-text">Remove</span>
                                    </button>
                                </div>
                            </div>
                        </Fragment>
                    )
                })}
            </fieldset>
        )
    }
}