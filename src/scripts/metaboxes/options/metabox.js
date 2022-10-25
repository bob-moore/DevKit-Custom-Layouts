import Select from '../../inputs/select.js';
import Label from '../../inputs/label.js';
import Text from '../../inputs/text.js';

import { sprintf, __ } from '@wordpress/i18n';
import { Component} from "@wordpress/element";

// noinspection JSUnresolvedVariable
export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            container: this.props.meta.container,
            class : this.props.meta.class,
        };
    }
    _update = ( value, key ) => {
        this.setState( { [key] : value, prevType : this.state.type } );
    }
    render() {
        return (
            <fieldset>
                <div className="field flex-unset" style={{width: '100%'}}>
                    <Label
                        for={sprintf( '%s[container]', this.props.meta.key )}
                        value={this.props.fields.container.label}
                        display={true}
                    />
                    <Select
                        field={this.props.fields.container}
                        name={sprintf( '%s[container]', this.props.meta.key )}
                        value={this.state.container}
                        isClearable={true}
                        isSearchable={false}
                        onChange={(value) => this._update( value, 'container' )}
                    />
                </div>

                { this.state.container !== '' &&
                    <div className='field flex-unset'>
                        <Label
                            for={sprintf( '%s[class]', this.props.meta.key )}
                            value={this.props.fields.class.label}
                            display={true}
                        />
                        <Text
                            field={this.props.fields.class}
                            name={sprintf( '%s[class]', this.props.meta.key )}
                            value={this.state.class}
                            onChange={(value) => this._update( value, 'class' )}
                        />
                    </div>
                }
            </fieldset>
        )
    }
}