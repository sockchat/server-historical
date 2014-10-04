class Message {
    static Seperator = String.fromCharCode(255);

    static Pack(id: number, ...params: string[]): string {
        return String.fromCharCode(id) + params.join(this.Seperator);
    }
}