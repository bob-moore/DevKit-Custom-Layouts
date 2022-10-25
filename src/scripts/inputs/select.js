import { Fragment, Component } from "@wordpress/element";
import Select from 'react-select'

export default class extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            value : this._getValues()
            // value : this.props.field.values[ this.props.value ] ?? this.props.field.default,
        };
    }
    _update = ( option ) =>
    {
        this.setState( { value : option }, () =>
            {
                let value = option instanceof Object ? option.value : '';
                this.props.onChange( value );
            }
        );
    }
    _getValues = () => {
        let values = '';

        if ( this.props.value instanceof Array )
        {
            if ( this.props.isMulti )
            {
                values = this.props.value.map( ( value, i ) => {
                    return this.props.field.values[ value ] ?? false;
                } );
            }
            else if ( this.props.value.length > 0 )
            {
                values = this.props.field.values[ this.props.value[0]];
            }
            else {
                values = this.props.field.default;
            }
        }
        else {
            values = this.props.field.values[ this.props.value ] ?? this.props.field.default;
        }

        return values ?? '';
    }
    // shouldComponentUpdate(nextProps, nextState)
    // {
    //     if ( nextProps === this.props )
    //     {
    //         return false;
    //     }
    //     return true;
    // }
    componentDidUpdate(prevProps, prevState, snapshot )
    {
        if ( prevProps.field.values === this.props.field.values )
        {
            return;
        }

        if ( this.props.field.values !== prevProps.field.values )
        {
            this.setState( { value : '' } );
        }
    }
    render()
    {
        return (
            <Fragment>
                <label
                    htmlFor={this.props.name}
                    className="screen-reader-text">
                    {this.props.label}
                </label>
                <Select
                    options={this.props.field.options}
                    name={this.props.isMulti ? false : this.props.name}
                    isClearable={this.props.isClearable || false}
                    isSearchable={this.props.isSearchable || false}
                    isMulti={this.props.isMulti || false}
                    value={this.state.value}
                    onChange={this._update}
                />
                { this.props.isMulti && this.state.value &&
                    <Fragment>
                        { this.state.value instanceof Array &&
                            this.state.value.map( ( option, i ) =>
                            {
                                return(
                                    <Fragment key={i + '_' + option.value}>
                                        <input type="hidden" value={option.value} name={this.props.name + '[' + i +']'} readOnly={true}/>
                                    </Fragment>
                                )
                            })
                        }
                    </Fragment>
                }
            </Fragment>
        )
    }
}