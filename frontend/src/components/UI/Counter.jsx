export const Counter = ({ count, onIncrement }) => {
    return (
        <button type="button" className="counter" onClick={onIncrement}>
            Count is {count}
        </button>
    );
};