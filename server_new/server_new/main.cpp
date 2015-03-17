#include <iostream>
#include <thread>
#include <mutex>

void shit(int a, int b, int &c) {
	c = a + b;
}

int main() {
	int tmp = 0;
	std::thread t(shit, 2, 3, std::ref(tmp));
	std::cout << tmp;
	return 0;
}