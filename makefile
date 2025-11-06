dirs:
	mkdir -v {tmp,out}

doc: 
	markdown README.md > README.html
