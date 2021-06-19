import './App.css';
import axios from 'axios';
import React,{Component} from 'react';

class App extends Component {

    constructor(props) {
        super(props);

        this.state = {
            invoicesFile: null,
            currencyRates: 'EUR:1,USD:0.987,GBP:0.878',
            outputCurrency: 'GBP',
            vatNumber: '',

            results: [],
            errorMessage: '',
        };

        this.handleInputChange = this.handleInputChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleInputChange(event) {
        const target = event.target;
        const value = target.type === 'file' ? target.files[0] : target.value;
        const name = target.name;

        this.setState({[name]: value});
    }

    handleSubmit(event) {
        // Create an object of formData
        const formData = new FormData();

        // Update the formData object
        if (this.state.invoicesFile) {
            formData.set("invoicesFile", this.state.invoicesFile, this.state.invoicesFile.name );
        }
        formData.set("currencyRates", this.state.currencyRates);
        formData.set("outputCurrency", this.state.outputCurrency);
        formData.set("vatNumber", this.state.vatNumber);

        // Initialize UI state to empty.
        this.setState({error: '', results: []});

        let that = this;
        axios.post("http://localhost:8000", formData)
            .then(function (response) {
                const totals = response.data.totals;
                that.setState({results: totals});
                return totals;
            })
            .catch(error => {
                if (error.response.status === 400) {
                    const message = error.response.data.error;
                    that.setState({error: message});
                }

                return []
            });

        event.preventDefault();
    }

    previewResults = () => {
        return this.state.results.map((result) => (
            <div key={result.vatNumber.toString()} className="aggregation-result">
                {result.name} - {result.total} {result.currencyCode}
            </div>
        ));
    };

    render() {
        return (
            <div className="App">

                <h1>Invoices calculator</h1>

                <form onSubmit={this.handleSubmit}>

                    <div>
                        <label>Invoices file:</label>
                        <input name="invoicesFile" type="file" onChange={this.handleInputChange} />
                    </div>

                    <div>
                        <label>Currency rates:</label>
                        <input name="currencyRates" type="text" value={this.state.currencyRates} onChange={this.handleInputChange} />
                    </div>

                    <div>
                        <label>Output currency:</label>
                        <input name="outputCurrency" type="text" value={this.state.outputCurrency} onChange={this.handleInputChange} />
                    </div>

                    <div>
                        <label>VAT Number:</label>
                        <input name="vatNumber" type="text" value={this.state.vatNumber} onChange={this.handleInputChange} />
                    </div>

                    <input type="submit" value="Submit" />

                </form>

                <div>{this.previewResults()}</div>

                <div className="error-class">{this.state.error}</div>

            </div>
        );
    }
}

export default App;
