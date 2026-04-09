dirs:
	mkdir -v {tmp,out}

doc: 
	markdown README.md > README.html
	markdown INDEXFILES.md > INDEXFILES.html
