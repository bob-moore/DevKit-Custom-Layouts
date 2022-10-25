import { Fragment, Component, createRef } from "@wordpress/element";
import { DateTimePicker } from '@wordpress/components';

export default class extends Component
{
    constructor(props)
    {
        super(props);
        this.state = {
            value : this.props.value,
            active : false
        };
        this.datePicker = createRef();
    }
    handleChange = ( value ) => {
        this.setState( { value : value }, () => {
            this.props.onChange( value );
        } );
    }
    _deActivateDatepicker = (e) => {
        if ( ! this.datePicker.current.contains(e.target) )
        {
            this.setState( { active : false } );
            document.removeEventListener('click', this._deActivateDatepicker);
        }
    }
    _activateDatepicker = () => {
        this.setState( { active : true } );
        document.addEventListener('click', this._deActivateDatepicker);
    }
    render()
    {
        return (
            <Fragment>
                <div className="date-input" ref={this.datePicker}>

                    <input type="text"
                           name={this.props.name}
                           value={this.state.value}
                           onFocus={this._activateDatepicker}
                           readOnly={true}
                           placeholder={"Choose Date"}
                    />
                    { this.state.active === true &&
                        <div className="datepicker-container" >
                            <div className="datepicker">
                                <DateTimePicker
                                    currentDate={this.state.value}
                                    onChange={this.handleChange}
                                    is12Hour={ true }
                                />
                            </div>
                        </div>
                    }
                </div>
            </Fragment>
        )
    }
}