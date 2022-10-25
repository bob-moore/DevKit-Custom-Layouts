// noinspection JSUnresolvedVariable
import { Fragment, Component} from "@wordpress/element";
import {__} from "@wordpress/i18n";
import {sprintf} from "@wordpress/i18n";
import Select from '../../inputs/select.js';
import Label from '../../inputs/label.js';
import Date from '../../inputs/date.js';
import Text from '../../inputs/text.js';

export default class extends Component
{
    constructor(props)
    {
        super(props);
        this.state = {
            type : this.props.type,
            subtype : this.props.subtype,
            deps : this.props.deps,
            id : this.props.id,
        };
    }

    _update = ( value, key ) => {
        this.setState( { [key] : value } );
    }
    render() {
        return (
            <Fragment>
                <div className="field">
                    <Label
                        for={sprintf( '%s[conditions][%d][context][%d][type]',
                            this.props.meta.key,
                            this.props.group,
                            this.props.index
                        )}
                        value={this.props.fields.type.label}
                        display={false}
                    />
                    <Select
                        field={this.props.fields.base}
                        name={sprintf( '%s[conditions][%d][context][%d][type]',
                            this.props.meta.key,
                            this.props.group,
                            this.props.index
                        )}
                        value={this.state.type}
                        isClearable={false}
                        isSearchable={false}
                        onChange={(value) => this._update( value, 'type' )}
                    />
                </div>
                { [ 'view', 'post_type', 'term', 'user', 'author', 'posts' ].includes( this.state.type ) &&
                    <Fragment>
                        <div className="field" style={{width : '8em', flex : 'unset'}}>
                            <Label
                                for={sprintf( '%s[conditions][%d][context][%d][subtype]',
                                    this.props.meta.key,
                                    this.props.group,
                                    this.props.index
                                )}
                                value={this.props.fields.comparison.label}
                                display={false}
                            />
                            <Select
                                field={this.props.fields.comparison}
                                name={sprintf( '%s[conditions][%d][context][%d][subtype]',
                                    this.props.meta.key,
                                    this.props.group,
                                    this.props.index
                                )}
                                value={this.state.subtype}
                                isClearable={false}
                                isSearchable={false}
                                onChange={(value) => this._update( value, 'subtype' )}
                            />
                        </div>
                        <div className="field">
                            <Label
                                for={sprintf( '%s[conditions][%d][context][%d][deps]',
                                    this.props.meta.key,
                                    this.props.group,
                                    this.props.index
                                )}
                                value={this.props.fields[ this.state.type ].label}
                                display={false}
                            />
                            <Select
                                field={this.props.fields[ this.state.type ]}
                                name={sprintf( '%s[conditions][%d][context][%d][deps]',
                                    this.props.meta.key,
                                    this.props.group,
                                    this.props.index
                                )}
                                value={this.state.deps}
                                isClearable={false}
                                isSearchable={false}
                                isMulti={this.props.fields[ this.state.type ].multiple}
                                onChange={(value) => this._update( value, 'deps' )}
                            />
                        </div>
                    </Fragment>
                }
                {
                    [ 'schedule' ].includes( this.state.type ) &&
                    <Fragment>
                    <div className="field" style={{width : '8em', flex : 'unset'}}>
                        <Label
                            for={sprintf( '%s[conditions][%d][context][%d][subtype]',
                                this.props.meta.key,
                                this.props.group,
                                this.props.index
                            )}
                            value={this.props.fields.schedule.label}
                            display={false}
                        />
                        <Select
                            field={this.props.fields.schedule}
                            name={sprintf( '%s[conditions][%d][context][%d][subtype]',
                                this.props.meta.key,
                                this.props.group,
                                this.props.index
                            )}
                            value={this.state.subtype}
                            isClearable={false}
                            isSearchable={false}
                            onChange={(value) => this._update( value, 'subtype' )}
                        />
                    </div>
                    <div className="field">
                        <Date
                            onChange={(value) => this._update( value, 'deps' )}
                            value={this.state.deps}
                            name={sprintf( '%s[conditions][%d][context][%d][deps]',
                                this.props.meta.key,
                                this.props.group,
                                this.props.index
                            )}
                        />
                    </div>
                    </Fragment>
                }
                {
                    this.state.type === 'custom' &&
                    <div className="field">
                        <Label
                            for={sprintf( '%s[conditions][%d][context][%d][id]',
                                this.props.meta.key,
                                this.props.group,
                                this.props.index
                            )}
                            value={__('Reference ID', 'devkit_layouts')}
                            display={false}
                        />
                        <Text
                            name={sprintf( '%s[conditions][%d][context][%d][id]',
                                this.props.meta.key,
                                this.props.group,
                                this.props.index
                            )}
                            value={this.state.id}
                            placeholder={__('Reference ID', 'devkit_layouts')}
                            onChange={(value) => this._update( value, 'id' )}
                        />
                    </div>
                }
            </Fragment>
        )
    }
}