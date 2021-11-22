import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';

// const e = React.createElement;

export default class ChatForm extends React.Component {
    constructor(props) {
        super(props);
        this.state = { to: '', message: '' };

        this.handleChange = this.handleChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;
        this.setState({
            [name]: value
        });
    }
    handleSubmit(event) {
        alert('A name was submitted: ' + this.state.to + " message:" + this.state.message);
        event.preventDefault();
    }

    render() {
        return (
            <div className="ChatForm">
                <form onSubmit={this.handleSubmit}>
                    <header className="ChatHeader">
                        LINE送信
                    </header>
                    <div className="ChatBody">
                        <div className="ChatRow">
                            <TextField id="outlined-basic" label="To" variant="outlined" margin="normal" name="to" type="text" value={this.state.to} onChange={this.handleChange} />
                        </div>
                        <div className="ChatRow">
                            <TextField id="outlined-basic" label="Message" variant="outlined" margin="normal" name="message" multiline minRows={5} type="text" value={this.state.message} onChange={this.handleChange} />
                        </div>
                        <Button variant="contained" type="submit">送信</Button>
                    </div>
                </form>
            </div>
        );
    }
}