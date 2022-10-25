import Label from '../../inputs/label.js';
import Code from '../../inputs/code.js';

import { sprintf, __ } from '@wordpress/i18n';
import { Component} from "@wordpress/element";



// noinspection JSUnresolvedVariable
export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            scripts : this.props.meta.scripts.raw,
            styles : this.props.meta.styles.raw,

        };
    }
    _update = ( value, key ) => {
        this.setState( { [key] : value } );
    }


    render() {
        return (
            <fieldset>
                <div className="field" style={{width: '100%', flex : 'unset'}}>
                    <div className={"flex-container"}>
                        <Label
                            for={sprintf( '%s[scripts][raw]', this.props.meta.key )}
                            value={__('Custom Javascript', 'devkit_layouts')}
                            display={true}
                        />

                    </div>

                    <Code
                        mode="javascript"
                        value={this.state.scripts}
                        name={sprintf( '%s[scripts][raw]', this.props.meta.key )}
                        onChange={(value) => this._update( value, 'scripts' )}
                    />
                </div>
                <div className="field" style={{width: '100%', flex : 'unset'}}>
                    <Label
                        for={sprintf( '%s[styles][raw]', this.props.meta.key )}
                        value={__('Custom (S)CSS', 'devkit_layouts')}
                        display={true}
                    />
                    <Code
                        mode="scss"
                        value={this.state.styles}
                        name={sprintf( '%s[styles][raw]', this.props.meta.key )}
                        onChange={(value) => this._update( value, 'styles' )}
                    />
                </div>
            </fieldset>
        )
    }
}