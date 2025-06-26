define([], function() {
    class IRRPlugin extends HyperFormula.FunctionPlugin {
        static implementedFunctions = {
            "IRR": {
                method: "irr",
                parameters: [
                    { argumentType: HyperFormula.FunctionArgumentType.RANGE },
                    { argumentType: HyperFormula.FunctionArgumentType.NUMBER, optionalArg: true }
                ],
            },
        };

        irr(ast, state) {
            try {
                const cashFlows = this.evaluateAst(ast.args[0], state);
                const guess = ast.args[1] ? this.evaluateAst(ast.args[1], state) : 0.1;
                const flatCashFlows = cashFlows.data.flat();
                if (flatCashFlows.length === 0) {
                    throw new Error("Invalid cash flow data provided to IRR function.");
                }
                const hasPositive = flatCashFlows.some(value => value > 0);
                const hasNegative = flatCashFlows.some(value => value < 0);
                if (!hasPositive || !hasNegative) {
                    return HyperFormula.ErrorType.NUM;
                }
                return this.calculateIRR(flatCashFlows, guess);
            } catch (error) {
                return HyperFormula.ErrorType.NUM;
            }
        }

        calculateIRR(cashFlows, guess) {
            const maxIterations = 1000;
            const precision = 1e-7;
            let irr = guess;
            for (let i = 0; i < maxIterations; i++) {
                const npv = cashFlows.reduce((acc, cashFlow, period) => acc + cashFlow / Math.pow(1 + irr, period), 0);
                const npvDerivative = cashFlows.reduce((acc, cashFlow, period) => acc - period * cashFlow / Math.pow(1 + irr, period + 1), 0);
                if (npvDerivative === 0) {
                    irr += 0.01;
                    continue;
                }
                const newIRR = irr - npv / npvDerivative;
                if (Math.abs(newIRR - irr) < precision) {
                    return newIRR.toFixed(2);
                }
                irr = newIRR;
            }
            return irr.toFixed(2);
        }
    }

    // Register the IRRPlugin and build the HyperFormula instance
    HyperFormula.registerFunctionPlugin(IRRPlugin, { enGB: { IRR: "IRR" } });
    return HyperFormula.buildEmpty({ licenseKey: "gpl-v3" });
});