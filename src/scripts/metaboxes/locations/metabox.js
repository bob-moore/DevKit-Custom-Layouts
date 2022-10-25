import { sprintf, __, } from '@wordpress/i18n';
import { Fragment, Component } from "@wordpress/element";
import Select from '../../inputs/select';
import Label from '../../inputs/label';
import Text from '../../inputs/text';
import Number from '../../inputs/number';

// noinspection JSUnresolvedVariable
export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            locations : this.props.meta.locations,
        };
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
        event.preventDefault();
        let locations = this.state.locations;
        locations.push( { hook : '', priority : 10 } );
        this.setState( { locations : locations } );
    }

    _setLocation = ( selection, index ) => {
        let locations = this.state.locations;
        locations[index].hook = selection;
        this.setState( { locations : locations } );
    }
    _setCustomLocation = ( value, index ) => {
        let locations = this.state.locations;
        locations[index].custom = value;
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
                { ! this.state.locations.length &&
                    <div className="field-group">
                        <div className="field">
                            <div className="devkit-notice devkit-notice-error">
                                <p>{__( 'No Locations have been selected.', 'devkit_layouts' )} <a href='#' onClick={this.addField}>{__( 'Create One Now', 'devkit_layouts' )}</a></p>
                            </div>
                        </div>
                    </div>
                }
                {this.state.locations.map((item, i) => {
                    return (
                        <fieldset key={sprintf( '%s_%d_%d', item.hook, item.priority, i )} data-group-order={i} className="locations-group">
                            <div className="field">
                                <Label
                                    for={sprintf( '%s[locations][%d][hook]', this.props.meta.key, i )}
                                    value={this.props.fields.hook.label}
                                    display={i === 0}
                                />
                                <Select
                                    field={this.props.fields.hook}
                                    name={sprintf( '%s[locations][%d][hook]', this.props.meta.key, i )}
                                    value={item.hook}
                                    isClearable={false}
                                    isSearchable={true}
                                    onChange={(value) => this._setLocation( value, i )}
                                />
                            </div>
                            { item.hook === 'custom' &&
                                <div className='field'>
                                    <Label
                                        for={sprintf( '%s[locations][%d][custom]', this.props.meta.key, i )}
                                        value={__('Custom Location', 'devkit_layouts')}
                                        display={i === 0}
                                    />
                                    <Text
                                        name={sprintf( '%s[locations][%d][custom]', this.props.meta.key, i )}
                                        value={item.custom ?? ''}
                                        onChange={(value) => this._setCustomLocation(value, i)}
                                        placeholder={__('Custom Location', 'devkit_layouts')}
                                    />
                                </div>
                            }
                            <div className="field" style={{width: '5em', flex : 'unset'}}>
                                <Label
                                    for={sprintf( '%s[locations][%d][priority]', this.props.meta.key, i )}
                                    value={this.props.fields.priority.label}
                                    display={i === 0}
                                />
                                <Number
                                    min="0"
                                    step="1"
                                    name={sprintf( '%s[locations][%d][priority]', this.props.meta.key, i )}
                                    value={this.state.locations[i].priority}
                                    onChange={ ( value ) => { this._updatePriority( value, i ) } }
                                />
                            </div>
                            <div className="field button-container" style={{width: '40px', flex : 'unset'}}>
                                <button className="devkit-button-icon button remove-control" onClick={() => this.removeField(i)}><span className="dashicons dashicons-remove"></span><span className="screen-reader-text">Remove</span></button>
                            </div>
                        </fieldset>
                    )
                })}
                <div className="field-group">
                    <div className="field group-controls">
                        <a href="#" className="add group-control" onClick={this.addField}>
                            {__( 'Add Location', 'devkit_layouts' )}
                        </a>
                    </div>
                </div>
            </Fragment>
        )
    }
}