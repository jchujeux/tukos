define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.isArgumentsArray = void 0;
    const isInArray = (array, value) => {
        let i, len;
        for (i = 0, len = array.length; i !== len; ++i) {
            if (array[i] === value) {
                return true;
            }
        }
        return false;
    };
    const mapValues = (mapper) => (obj) => {
        //const result = {};
        Object.keys(obj).forEach((key) => {
            obj[key] = mapper(obj[key]);
        });
        //return result;
        return obj;
    };
    const convertKeys = (converter) => (obj) => {
        const newKeys = Object.keys(obj)
            .map((key) => (obj.hasOwnProperty(key) ? [key, converter(key)] : null))
            .filter((val) => val != null);
        newKeys.forEach(([oldKey, newKey]) => {
            if (oldKey !== newKey) {
                obj[newKey] = obj[oldKey];
                delete obj[oldKey];
            }
        });
        return obj;
    };
    const isArgumentsArray = (args) => Array.isArray(args) && args.isArgumentsArray;
    exports.isArgumentsArray = isArgumentsArray;
    const thunkEvaluator = (val) => evaluate(val);
    const objEvaluator = mapValues(thunkEvaluator);
    const evaluate = (thunkExpression) => {
        if (typeof thunkExpression === "function" && thunkExpression.length === 0) {
            return evaluate(thunkExpression());
        }
        else if ((0, exports.isArgumentsArray)(thunkExpression)) {
            return thunkExpression.map((val) => evaluate(val()));
        }
        else if (Array.isArray(thunkExpression)) {
            return thunkExpression.map(thunkEvaluator);
        }
        else if (typeof thunkExpression === "object") {
            return objEvaluator(thunkExpression);
        }
        else {
            return thunkExpression;
        }
    };
    const thunk = (delegate, ...args) => () => delegate(...args);
    ;
    class ExpressionParser {
        constructor(options) {
            this.options = options;
            this.surroundingOpen = {};
            this.surroundingClose = {};
            if (this.options.SURROUNDING) {
                Object.keys(this.options.SURROUNDING).forEach((key) => {
                    const item = this.options.SURROUNDING[key];
                    let open = item.OPEN;
                    let close = item.CLOSE;
                    if (this.options.isCaseInsensitive) {
                        key = key.toUpperCase();
                        open = open.toUpperCase();
                        close = close.toUpperCase();
                    }
                    this.surroundingOpen[open] = true;
                    this.surroundingClose[close] = {
                        OPEN: open,
                        ALIAS: key,
                    };
                });
            }
            if (this.options.isCaseInsensitive) {
                // convert all terms to uppercase
                const upperCaser = (key) => key.toUpperCase();
                const upperCaseKeys = convertKeys(upperCaser);
                const upperCaseVals = mapValues(upperCaser);
                upperCaseKeys(this.options.INFIX_OPS);
                upperCaseKeys(this.options.PREFIX_OPS);
                upperCaseKeys(this.options.AMBIGUOUS);
                upperCaseVals(this.options.AMBIGUOUS);
                this.options.PRECEDENCE = this.options.PRECEDENCE.map((arr) => arr.map((val) => val.toUpperCase()));
            }
            if (this.options.LITERAL_OPEN) {
                this.LIT_CLOSE_REGEX = new RegExp(`${this.options.LITERAL_OPEN}\$`);
            }
            if (this.options.LITERAL_CLOSE) {
                this.LIT_OPEN_REGEX = new RegExp(`^${this.options.LITERAL_CLOSE}`);
            }
            this.symbols = {};
            this.options.SYMBOLS.forEach((symbol) => {
                this.symbols[symbol] = symbol;
            });
        }
        resolveCase(key) {
            return this.options.isCaseInsensitive ? key.toUpperCase() : key;
        }
        resolveAmbiguity(token) {
            return this.options.AMBIGUOUS[this.resolveCase(token)];
        }
        isSymbol(char) {
            return this.symbols[char] === char;
        }
        getPrefixOp(op) {
            if (this.options.termTyper && this.options.termTyper(op) === "function") {
                const termValue = this.options.termDelegate(op);
                if (typeof termValue !== "function") {
                    throw new Error(`${op} is not a function.`);
                }
                const result = termValue;
                return (argsThunk) => {
                    const args = evaluate(argsThunk);
                    if (!Array.isArray(args)) {
                        return () => result(args);
                    }
                    else {
                        return () => result(...args);
                    }
                };
            }
            return this.options.PREFIX_OPS[this.resolveCase(op)];
        }
        getInfixOp(op) {
            return this.options.INFIX_OPS[this.resolveCase(op)];
        }
        getPrecedence(op) {
            let i, len, casedOp;
            if (this.options.termTyper && this.options.termTyper(op) === "function") {
                return 0;
            }
            casedOp = this.resolveCase(op);
            for (i = 0, len = this.options.PRECEDENCE.length; i !== len; ++i) {
                if (isInArray(this.options.PRECEDENCE[i], casedOp)) {
                    return i;
                }
            }
            return i;
        }
        tokenize(expression) {
            let token = "";
            const EOF = 0;
            const tokens = [];
            const state = {
                startedWithSep: true,
                scanningLiteral: false,
                scanningSymbols: false,
                escaping: false,
            };
            const endWord = (endedWithSep) => {
                if (token !== "") {
                    const disambiguated = this.resolveAmbiguity(token);
                    if (disambiguated && state.startedWithSep && !endedWithSep) {
                        // ambiguous operator is nestled with the RHS
                        // treat it as a prefix operator
                        tokens.push(disambiguated);
                    }
                    else {
                        // TODO: break apart joined surroundingOpen/Close
                        tokens.push(token);
                    }
                    token = "";
                    state.startedWithSep = false;
                }
            };
            const chars = expression.split("");
            let currChar;
            let i, len;
            for (i = 0, len = chars.length; i <= len; ++i) {
                if (i === len) {
                    currChar = EOF;
                }
                else {
                    currChar = chars[i];
                }
                if (currChar === this.options.ESCAPE_CHAR && !state.escaping) {
                    state.escaping = true;
                    continue;
                }
                else if (state.escaping) {
                    token += currChar;
                }
                else if (currChar === this.options.LITERAL_OPEN &&
                    !state.scanningLiteral) {
                    state.scanningLiteral = true;
                    endWord(false);
                }
                else if (currChar === this.options.LITERAL_CLOSE) {
                    state.scanningLiteral = false;
                    tokens.push(this.options.LITERAL_OPEN + token + this.options.LITERAL_CLOSE);
                    token = "";
                }
                else if (currChar === EOF) {
                    endWord(true);
                }
                else if (state.scanningLiteral) {
                    token += currChar;
                }
                else if (currChar === this.options.SEPARATOR) {
                    endWord(true);
                    state.startedWithSep = true;
                }
                else if (currChar === this.options.GROUP_OPEN ||
                    currChar === this.options.GROUP_CLOSE) {
                    endWord(currChar === this.options.GROUP_CLOSE);
                    state.startedWithSep = currChar === this.options.GROUP_OPEN;
                    tokens.push(currChar);
                }
                else if (currChar in this.surroundingOpen ||
                    currChar in this.surroundingClose) {
                    endWord(currChar in this.surroundingClose);
                    state.startedWithSep = currChar in this.surroundingOpen;
                    tokens.push(currChar);
                }
                else if ((this.isSymbol(currChar) && !state.scanningSymbols) ||
                    (!this.isSymbol(currChar) && state.scanningSymbols)) {
                    endWord(false);
                    token += currChar;
                    state.scanningSymbols = !state.scanningSymbols;
                }
                else {
                    token += currChar;
                }
                state.escaping = false;
            }
            return tokens;
        }
        tokensToRpn(tokens) {
            let token;
            let i, len;
            let isInfix, isPrefix, surroundingToken, lastInStack, tokenPrecedence;
            const output = [];
            const stack = [];
            const grouping = [];
            for (i = 0, len = tokens.length; i !== len; ++i) {
                token = tokens[i];
                isInfix = typeof this.getInfixOp(token) !== "undefined";
                isPrefix = typeof this.getPrefixOp(token) !== "undefined";
                if (isInfix || isPrefix) {
                    tokenPrecedence = this.getPrecedence(token);
                    lastInStack = stack[stack.length - 1];
                    while (lastInStack &&
                        ((!!this.getPrefixOp(lastInStack) &&
                            this.getPrecedence(lastInStack) < tokenPrecedence) ||
                            (!!this.getInfixOp(lastInStack) &&
                                this.getPrecedence(lastInStack) <= tokenPrecedence))) {
                        output.push(stack.pop());
                        lastInStack = stack[stack.length - 1];
                    }
                    stack.push(token);
                }
                else if (this.surroundingOpen[token]) {
                    stack.push(token);
                    grouping.push(token);
                }
                else if (this.surroundingClose[token]) {
                    surroundingToken = this.surroundingClose[token];
                    if (grouping.pop() !== surroundingToken.OPEN) {
                        throw new Error(`Mismatched Grouping (unexpected closing "${token}")`);
                    }
                    token = stack.pop();
                    while (token !== surroundingToken.OPEN &&
                        typeof token !== "undefined") {
                        output.push(token);
                        token = stack.pop();
                    }
                    if (typeof token === "undefined") {
                        throw new Error("Mismatched Grouping");
                    }
                    stack.push(surroundingToken.ALIAS);
                }
                else if (token === this.options.GROUP_OPEN) {
                    stack.push(token);
                    grouping.push(token);
                }
                else if (token === this.options.GROUP_CLOSE) {
                    if (grouping.pop() !== this.options.GROUP_OPEN) {
                        throw new Error(`Mismatched Grouping (unexpected closing "${token}")`);
                    }
                    token = stack.pop();
                    while (token !== this.options.GROUP_OPEN &&
                        typeof token !== "undefined") {
                        output.push(token);
                        token = stack.pop();
                    }
                    if (typeof token === "undefined") {
                        throw new Error("Mismatched Grouping");
                    }
                }
                else {
                    output.push(token);
                }
            }
            for (i = 0, len = stack.length; i !== len; ++i) {
                token = stack.pop();
                surroundingToken = this.surroundingClose[token];
                if (surroundingToken && grouping.pop() !== surroundingToken.OPEN) {
                    throw new Error(`Mismatched Grouping (unexpected closing "${token}")`);
                }
                else if (token === this.options.GROUP_CLOSE &&
                    grouping.pop() !== this.options.GROUP_OPEN) {
                    throw new Error(`Mismatched Grouping (unexpected closing "${token}")`);
                }
                output.push(token);
            }
            if (grouping.length !== 0) {
                throw new Error(`Mismatched Grouping (unexpected "${grouping.pop()}")`);
            }
            return output;
        }
        evaluateRpn(stack, infixer, prefixer, terminator, terms) {
            let lhs, rhs;
            const token = stack.pop();
            if (typeof token === "undefined") {
                throw new Error("Parse Error: unexpected EOF");
            }
            const infixDelegate = this.getInfixOp(token);
            const prefixDelegate = this.getPrefixOp(token);
            const isInfix = infixDelegate && stack.length > 1;
            const isPrefix = prefixDelegate && stack.length > 0;
            if (isInfix || isPrefix) {
                rhs = this.evaluateRpn(stack, infixer, prefixer, terminator, terms);
            }
            if (isInfix) {
                lhs = this.evaluateRpn(stack, infixer, prefixer, terminator, terms);
                return infixer(token, lhs, rhs);
            }
            else if (isPrefix) {
                return prefixer(token, rhs);
            }
            else {
                return terminator(token, terms);
            }
        }
        rpnToExpression(stack) {
            const infixExpr = (term, lhs, rhs) => this.options.GROUP_OPEN +
                lhs +
                this.options.SEPARATOR +
                term +
                this.options.SEPARATOR +
                rhs +
                this.options.GROUP_CLOSE;
            const prefixExpr = (term, rhs) => (this.isSymbol(term) ? term : term + this.options.SEPARATOR) +
                this.options.GROUP_OPEN +
                rhs +
                this.options.GROUP_CLOSE;
            const termExpr = (term) => term;
            return this.evaluateRpn(stack, infixExpr, prefixExpr, termExpr);
        }
        rpnToTokens(stack) {
            const infixExpr = (term, lhs, rhs) => [this.options.GROUP_OPEN]
                .concat(lhs)
                .concat([term])
                .concat(rhs)
                .concat([this.options.GROUP_CLOSE]);
            const prefixExpr = (term, rhs) => [term, this.options.GROUP_OPEN]
                .concat(rhs)
                .concat([this.options.GROUP_CLOSE]);
            const termExpr = (term) => [term];
            return this.evaluateRpn(stack, infixExpr, prefixExpr, termExpr);
        }
        rpnToThunk(stack, terms) {
            const infixExpr = (term, lhs, rhs) => thunk(this.getInfixOp(term), lhs, rhs);
            const prefixExpr = (term, rhs) => thunk(this.getPrefixOp(term), rhs);
            const termExpr = (term, terms) => {
                if (this.options.LITERAL_OPEN &&
                    term.startsWith(this.options.LITERAL_OPEN)) {
                    // Literal string
                    return () => term
                        .replace(this.LIT_OPEN_REGEX, "")
                        .replace(this.LIT_CLOSE_REGEX, "");
                }
                else {
                    return (terms && term in terms) ? (() => terms[term]) : thunk(this.options.termDelegate, term);
                }
            };
            return this.evaluateRpn(stack, infixExpr, prefixExpr, termExpr, terms);
        }
        rpnToValue(stack, terms) {
            return evaluate(this.rpnToThunk(stack, terms));
        }
        thunkToValue(thunk) {
            return evaluate(thunk);
        }
        expressionToRpn(expression) {
            return this.tokensToRpn(this.tokenize(expression));
        }
        expressionToThunk(expression, terms) {
            return this.rpnToThunk(this.expressionToRpn(expression), terms);
        }
        expressionToValue(expression, terms) {
            return expression ? this.rpnToValue(this.expressionToRpn(expression), terms) : expression;
        }
        tokensToValue(tokens) {
            return this.rpnToValue(this.tokensToRpn(tokens));
        }
        tokensToThunk(tokens) {
            return this.rpnToThunk(this.tokensToRpn(tokens));
        }
    }
    exports.default = ExpressionParser;
});