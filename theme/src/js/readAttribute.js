export default (
    element,
    attributeName,
    {
        transform = (value) => value,
        validate = () => true,
        expectation,
    } = {},
) => {
    const value = element.getAttribute(attributeName);
    const transformedValue = transform(value);
    if (!validate(transformedValue)) {
        throw new Error(`Expected attribute ${attributeName} of element ${element} to be ${expectation}; got ${transformedValue} instead (${value} before the transform function was applied).`);
    }
    return transformedValue;
};
