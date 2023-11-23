let goldncrash_f = (data) => {
	return {
	  color: data[0],
	  value: data[1],
	};
  };
  
  /*
  * Game Constants
  */
  
  const YELLOW = 'yellow';
  const PURPLE = 'purple';
  const GREEN = 'green';
  const BLUE = 'blue';
  const GREY = 'grey';
  const RED = 'red';
  
  // prettier-ignore
  const CARDS_DATA = {
  1 : goldncrash_f([GREEN, 1]),
  2 : goldncrash_f([GREEN, 1]),
  3 : goldncrash_f([GREEN, 1]),
  4 : goldncrash_f([GREEN, 1]),
  5 : goldncrash_f([GREEN, 2]),
  6 : goldncrash_f([GREEN, 2]),
  7 : goldncrash_f([GREEN, 2]),
  8 : goldncrash_f([GREEN, 2]),
  9 : goldncrash_f([GREEN, 3]),
  10 : goldncrash_f([GREEN, 3]),
  11 : goldncrash_f([GREEN, 3]),
  12 : goldncrash_f([GREEN, 3]),
  13 : goldncrash_f([RED, 1]),
  14 : goldncrash_f([RED, 1]),
  15 : goldncrash_f([RED, 1]),
  16 : goldncrash_f([RED, 1]),
  17 : goldncrash_f([RED, 2]),
  18 : goldncrash_f([RED, 2]),
  19 : goldncrash_f([RED, 2]),
  20 : goldncrash_f([RED, 2]),
  21 : goldncrash_f([RED, 3]),
  22 : goldncrash_f([RED, 3]),
  23 : goldncrash_f([RED, 3]),
  24 : goldncrash_f([RED, 3]),
  25 : goldncrash_f([BLUE, 1]),
  26 : goldncrash_f([BLUE, 1]),
  27 : goldncrash_f([BLUE, 1]),
  28 : goldncrash_f([BLUE, 1]),
  29 : goldncrash_f([BLUE, 1]),
  30 : goldncrash_f([BLUE, 2]),
  31 : goldncrash_f([BLUE, 2]),
  32 : goldncrash_f([BLUE, 2]),
  33 : goldncrash_f([BLUE, 2]),
  34 : goldncrash_f([BLUE, 2]),
  35 : goldncrash_f([BLUE, 3]),
  36 : goldncrash_f([BLUE, 3]),
  37 : goldncrash_f([BLUE, 3]),
  38 : goldncrash_f([BLUE, 3]),
  39 : goldncrash_f([BLUE, 3]),
  40 : goldncrash_f([YELLOW, 1]),
  41 : goldncrash_f([YELLOW, 1]),
  42 : goldncrash_f([YELLOW, 1]),
  43 : goldncrash_f([YELLOW, 1]),
  44 : goldncrash_f([YELLOW, 1]),
  45 : goldncrash_f([YELLOW, 2]),
  46 : goldncrash_f([YELLOW, 2]),
  47 : goldncrash_f([YELLOW, 2]),
  48 : goldncrash_f([YELLOW, 2]),
  49 : goldncrash_f([YELLOW, 2]),
  50 : goldncrash_f([YELLOW, 3]),
  51 : goldncrash_f([YELLOW, 3]),
  52 : goldncrash_f([YELLOW, 3]),
  53 : goldncrash_f([YELLOW, 3]),
  54 : goldncrash_f([YELLOW, 3]),
  55 : goldncrash_f([PURPLE, 1]),
  56 : goldncrash_f([PURPLE, 1]),
  57 : goldncrash_f([PURPLE, 1]),
  58 : goldncrash_f([PURPLE, 1]),
  59 : goldncrash_f([PURPLE, 2]),
  60 : goldncrash_f([PURPLE, 2]),
  61 : goldncrash_f([PURPLE, 2]),
  62 : goldncrash_f([PURPLE, 2]),
  63 : goldncrash_f([PURPLE, 3]),
  64 : goldncrash_f([PURPLE, 3]),
  65 : goldncrash_f([PURPLE, 3]),
  66 : goldncrash_f([PURPLE, 3]),
  67 : goldncrash_f([GREY, 1]),
  68 : goldncrash_f([GREY, 1]),
  69 : goldncrash_f([GREY, 1]),
  70 : goldncrash_f([GREY, 1]),
  71 : goldncrash_f([GREY, 2]),
  72 : goldncrash_f([GREY, 2]),
  73 : goldncrash_f([GREY, 2]),
  74 : goldncrash_f([GREY, 2]),
  75 : goldncrash_f([GREY, 3]),
  76 : goldncrash_f([GREY, 3]),
  77 : goldncrash_f([GREY, 3]),
  78 : goldncrash_f([GREY, 3]),
  'back' : goldncrash_f(["", 1, ""])
  };