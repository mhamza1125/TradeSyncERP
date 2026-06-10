// Lot ranges with precomputed sample sizes for levels I, II, III
const LotRanges = [
    { Min: 2, Max: 8, CodeI: "A", CodeII: "A", CodeIII: "B",CodeS1: "A" ,CodeS2: "A" ,CodeS3: "A" ,CodeS4: "A" ,SampleSizeI: 2, SampleSizeII: 2, SampleSizeIII: 3 , SampleSizeS1: 2 , SampleSizeS2: 2, SampleSizeS3: 2, SampleSizeS4: 2},
    { Min: 9, Max: 15, CodeI: "A", CodeII: "B", CodeIII: "C",CodeS1: "A" ,CodeS2: "A" ,CodeS3: "A",CodeS4: "A"  ,SampleSizeI: 2, SampleSizeII: 3, SampleSizeIII: 5 , SampleSizeS1: 2 , SampleSizeS2: 2, SampleSizeS3: 2, SampleSizeS4: 2},
    { Min: 16, Max: 25, CodeI: "B", CodeII: "C", CodeIII: "D",CodeS1: "A",CodeS2: "A" ,CodeS3: "B",CodeS4: "B"  ,SampleSizeI: 3, SampleSizeII: 5, SampleSizeIII: 8 , SampleSizeS1: 2 , SampleSizeS2: 2, SampleSizeS3: 3, SampleSizeS4: 3},
    { Min: 26, Max: 50, CodeI: "C", CodeII: "D", CodeIII: "E",CodeS1: "A",CodeS2: "B" ,CodeS3: "B",CodeS4: "C"  ,SampleSizeI: 5, SampleSizeII: 8, SampleSizeIII: 13 , SampleSizeS1: 2 , SampleSizeS2: 3, SampleSizeS3: 3, SampleSizeS4: 5},
    { Min: 51, Max: 90, CodeI: "C", CodeII: "E", CodeIII: "F",CodeS1: "B",CodeS2: "B" ,CodeS3: "C" ,CodeS4: "C" ,SampleSizeI: 5, SampleSizeII: 13, SampleSizeIII: 20 , SampleSizeS1: 3 , SampleSizeS2: 3, SampleSizeS3: 5, SampleSizeS4: 5},
    { Min: 91, Max: 150, CodeI: "D", CodeII: "F", CodeIII: "G",CodeS1: "B",CodeS2: "B" ,CodeS3: "C",CodeS4: "D"  ,SampleSizeI: 8, SampleSizeII: 20, SampleSizeIII: 32 , SampleSizeS1: 3 , SampleSizeS2: 3, SampleSizeS3: 5, SampleSizeS4: 8},
    { Min: 151, Max: 280, CodeI: "E", CodeII: "G", CodeIII: "H",CodeS1: "B",CodeS2: "C" ,CodeS3: "D",CodeS4: "E"  ,SampleSizeI: 13, SampleSizeII: 32, SampleSizeIII: 50 , SampleSizeS1: 3 , SampleSizeS2: 5, SampleSizeS3: 8, SampleSizeS4: 13},
    { Min: 281, Max: 500, CodeI: "F", CodeII: "H", CodeIII: "J",CodeS1: "B",CodeS2: "C" ,CodeS3: "D",CodeS4: "E"  ,SampleSizeI: 20, SampleSizeII: 50, SampleSizeIII: 80 , SampleSizeS1: 3 , SampleSizeS2: 5, SampleSizeS3: 8, SampleSizeS4: 13},
    { Min: 501, Max: 1200, CodeI: "G", CodeII: "J", CodeIII: "K",CodeS1: "C",CodeS2: "C" ,CodeS3: "E",CodeS4: "F"  ,SampleSizeI: 32, SampleSizeII: 80, SampleSizeIII: 125 , SampleSizeS1: 5 , SampleSizeS2: 5, SampleSizeS3: 13, SampleSizeS4: 20},
    { Min: 1201, Max: 3200, CodeI: "H", CodeII: "K", CodeIII: "L",CodeS1: "C",CodeS2: "D" ,CodeS3: "E",CodeS4: "G"  , SampleSizeI: 50, SampleSizeII: 125, SampleSizeIII: 200 , SampleSizeS1: 5 , SampleSizeS2: 8, SampleSizeS3: 13, SampleSizeS4: 32},
    { Min: 3201, Max: 10000, CodeI: "J", CodeII: "L", CodeIII: "M",CodeS1: "C",CodeS2: "D" ,CodeS3: "F",CodeS4: "G"  , SampleSizeI: 80, SampleSizeII: 200, SampleSizeIII: 315 , SampleSizeS1: 5 , SampleSizeS2: 8, SampleSizeS3: 20, SampleSizeS4: 32},
    { Min: 10001, Max: 35000, CodeI: "K", CodeII: "M", CodeIII: "N",CodeS1: "C",CodeS2: "D" ,CodeS3: "F",CodeS4: "H"  , SampleSizeI: 125, SampleSizeII: 315, SampleSizeIII: 500 , SampleSizeS1: 5 , SampleSizeS2: 8, SampleSizeS3: 20, SampleSizeS4: 50},
    { Min: 35001, Max: 150000, CodeI: "L", CodeII: "N", CodeIII: "P",CodeS1: "D",CodeS2: "E" ,CodeS3: "G",CodeS4: "J"  , SampleSizeI: 200, SampleSizeII: 500, SampleSizeIII: 800 , SampleSizeS1: 8 , SampleSizeS2: 13, SampleSizeS3: 32, SampleSizeS4: 80},
    { Min: 150001, Max: 500000, CodeI: "M", CodeII: "P", CodeIII: "Q",CodeS1: "D",CodeS2: "E" ,CodeS3: "G",CodeS4: "J"  , SampleSizeI: 315, SampleSizeII: 800, SampleSizeIII: 1250 , SampleSizeS1: 8 , SampleSizeS2: 13, SampleSizeS3: 32, SampleSizeS4: 80},
    { Min: 500001, Max: Number.MAX_SAFE_INTEGER, CodeI: "N", CodeII: "Q", CodeIII: "R",CodeS1: "D",CodeS2: "H"  ,CodeS3: "A",CodeS4: "K" , SampleSizeI: 500, SampleSizeII: 1250, SampleSizeIII: 2000 , SampleSizeS1: 8 , SampleSizeS2: 13, SampleSizeS3: 50, SampleSizeS4: 125}
];

// AQL numbers
const AqlNumbers = {
    "Not_Allowed": { 2: { Ac: 0, Re: 0 }, 3: { Ac: 0, Re: 0 }, 5: { Ac: 0, Re: 0 }, 8: { Ac: 0, Re: 0 }, 13: { Ac: 0, Re: 0 }, 20: { Ac: 0, Re: 0 }, 32: { Ac: 0, Re: 0 }, 50: { Ac: 0, Re: 0 }, 80: { Ac: 0, Re: 0 }, 125: { Ac: 0, Re: 0 }, 200: { Ac: 0, Re: 0 }, 315: { Ac: 0, Re: 0 }, 500: { Ac: 0, Re: 0 }, 800: { Ac: 0, Re: 0 }, 1250: { Ac: 0, Re: 0 }, 2000: { Ac: 0, Re: 0 } },
    "0.065": { 2: { Ac: 0, Re: 1, Ss: 200 }, 3: { Ac: 0, Re: 1, Ss: 200 }, 5: { Ac: 0, Re: 1, Ss: 200 }, 8: { Ac: 0, Re: 1, Ss: 200 }, 13: { Ac: 0, Re: 1, Ss: 200 }, 20: { Ac: 0, Re: 1, Ss: 200 }, 32: { Ac: 0, Re: 1, Ss: 200 }, 50: { Ac: 0, Re: 1, Ss: 200 }, 80: { Ac: 0, Re: 1, Ss: 200 }, 125: { Ac: 0, Re: 1, Ss: 200 }, 200: { Ac: 0, Re: 1 }, 315: { Ac: 0, Re: 1, Ss: 200 }, 500: { Ac: 1, Re: 2, Ss: 800 }, 800: { Ac: 1, Re: 2 }, 1250: { Ac: 2, Re: 3 }, 2000: { Ac: 3, Re: 4 } },
    "0.10": { 2: { Ac: 0, Re: 1, Ss: 125 }, 3: { Ac: 0, Re: 1, Ss: 125 }, 5: { Ac: 0, Re: 1, Ss: 125 }, 8: { Ac: 0, Re: 1, Ss: 125 }, 13: { Ac: 0, Re: 1, Ss: 125 }, 20: { Ac: 0, Re: 1, Ss: 125 }, 32: { Ac: 0, Re: 1, Ss: 125 }, 50: { Ac: 0, Re: 1, Ss: 125 }, 80: { Ac: 0, Re: 1, Ss: 125 }, 125: { Ac: 0, Re: 1 }, 200: { Ac: 0, Re: 1, Ss: 125 }, 315: { Ac: 1, Re: 2, Ss: 500 }, 500: { Ac: 1, Re: 2 }, 800: { Ac: 2, Re: 3 }, 1250: { Ac: 3, Re: 4 }, 2000: { Ac: 5, Re: 6 } },
    "0.15": { 2: { Ac: 0, Re: 1, Ss: 80 }, 3: { Ac: 0, Re: 1, Ss: 80 }, 5: { Ac: 0, Re: 1, Ss: 80 }, 8: { Ac: 0, Re: 1, Ss: 80 }, 13: { Ac: 0, Re: 1, Ss: 80 }, 20: { Ac: 0, Re: 1, Ss: 80 }, 32: { Ac: 0, Re: 1, Ss: 80 }, 50: { Ac: 0, Re: 1, Ss: 80 }, 80: { Ac: 0, Re: 1 }, 125: { Ac: 0, Re: 1, Ss: 80 }, 200: { Ac: 1, Re: 2, Ss: 315 }, 315: { Ac: 1, Re: 2 }, 500: { Ac: 2, Re: 3 }, 800: { Ac: 3, Re: 4 }, 1250: { Ac: 5, Re: 6 }, 2000: { Ac: 7, Re: 8 } },
    "0.25": { 2: { Ac: 0, Re: 1, Ss: 50 }, 3: { Ac: 0, Re: 1, Ss: 50 }, 5: { Ac: 0, Re: 1, Ss: 50 }, 8: { Ac: 0, Re: 1, Ss: 50 }, 13: { Ac: 0, Re: 1, Ss: 50 }, 20: { Ac: 0, Re: 1, Ss: 50 }, 32: { Ac: 0, Re: 1, Ss: 50 }, 50: { Ac: 0, Re: 1 }, 80: { Ac: 0, Re: 1, Ss: 50 }, 125: { Ac: 1, Re: 2, Ss: 200 }, 200: { Ac: 1, Re: 2 }, 315: { Ac: 2, Re: 3 }, 500: { Ac: 3, Re: 4 }, 800: { Ac: 5, Re: 6 }, 1250: { Ac: 7, Re: 8 }, 2000: { Ac: 10, Re: 11 } },
    "0.40": { 2: { Ac: 0, Re: 1, Ss: 32 }, 3: { Ac: 0, Re: 1, Ss: 32 }, 5: { Ac: 0, Re: 1, Ss: 32 }, 8: { Ac: 0, Re: 1, Ss: 32 }, 13: { Ac: 0, Re: 1, Ss: 32 }, 20: { Ac: 0, Re: 1, Ss: 32 }, 32: { Ac: 0, Re: 1 }, 50: { Ac: 0, Re: 1, Ss: 32 }, 80: { Ac: 1, Re: 2, Ss: 125 }, 125: { Ac: 1, Re: 2 }, 200: { Ac: 2, Re: 3 }, 315: { Ac: 3, Re: 4 }, 500: { Ac: 5, Re: 6 }, 800: { Ac: 7, Re: 8 }, 1250: { Ac: 10, Re: 11 }, 2000: { Ac: 14, Re: 15 } },
    "0.65": { 2: { Ac: 0, Re: 1, Ss: 20 }, 3: { Ac: 0, Re: 1, Ss: 20 }, 5: { Ac: 0, Re: 1, Ss: 20 }, 8: { Ac: 0, Re: 1, Ss: 20 }, 13: { Ac: 0, Re: 1, Ss: 20 }, 20: { Ac: 0, Re: 1 }, 32: { Ac: 0, Re: 1, Ss: 20 }, 50: { Ac: 1, Re: 2, Ss: 80 }, 80: { Ac: 1, Re: 2 }, 125: { Ac: 2, Re: 3 }, 200: { Ac: 3, Re: 4 }, 315: { Ac: 5, Re: 6 }, 500: { Ac: 7, Re: 8 }, 800: { Ac: 10, Re: 11 }, 1250: { Ac: 14, Re: 15 }, 2000: { Ac: 21, Re: 22 } },
    "1.0": { 2: { Ac: 0, Re: 1, Ss: 13 }, 3: { Ac: 0, Re: 1, Ss: 13 }, 5: { Ac: 0, Re: 1, Ss: 13 }, 8: { Ac: 0, Re: 1, Ss: 13 }, 13: { Ac: 0, Re: 1 }, 20: { Ac: 0, Re: 1, Ss: 13 }, 32: { Ac: 1, Re: 2, Ss: 50 }, 50: { Ac: 1, Re: 2 }, 80: { Ac: 2, Re: 3 }, 125: { Ac: 3, Re: 4 }, 200: { Ac: 5, Re: 6 }, 315: { Ac: 7, Re: 8 }, 500: { Ac: 10, Re: 11 }, 800: { Ac: 14, Re: 15 }, 1250: { Ac: 21, Re: 22 }, 2000: { Ac: 21, Re: 22, Ss: 1250 } },
    "1.5": { 2: { Ac: 0, Re: 1, Ss: 8 }, 3: { Ac: 0, Re: 1, Ss: 8 }, 5: { Ac: 0, Re: 1, Ss: 8 }, 8: { Ac: 0, Re: 1 }, 13: { Ac: 0, Re: 1, Ss: 8 }, 20: { Ac: 1, Re: 2, Ss: 32 }, 32: { Ac: 1, Re: 2 }, 50: { Ac: 2, Re: 3 }, 80: { Ac: 3, Re: 4 }, 125: { Ac: 5, Re: 6 }, 200: { Ac: 7, Re: 8 }, 315: { Ac: 10, Re: 11 }, 500: { Ac: 14, Re: 15 }, 800: { Ac: 21, Re: 22 }, 1250: { Ac: 21, Re: 22, Ss: 800 }, 2000: { Ac: 21, Re: 22, Ss: 800 } },
    "2.5": { 2: { Ac: 0, Re: 1, Ss: 5 }, 3: { Ac: 0, Re: 1, Ss: 5 }, 5: { Ac: 0, Re: 1 }, 8: { Ac: 0, Re: 1, Ss: 5 }, 13: { Ac: 1, Re: 2, Ss: 20 }, 20: { Ac: 1, Re: 2 }, 32: { Ac: 2, Re: 3 }, 50: { Ac: 3, Re: 4 }, 80: { Ac: 5, Re: 6 }, 125: { Ac: 7, Re: 8 }, 200: { Ac: 10, Re: 11 }, 315: { Ac: 14, Re: 15 }, 500: { Ac: 21, Re: 22 }, 800: { Ac: 21, Re: 22, Ss: 500 }, 1250: { Ac: 21, Re: 22, Ss: 500 }, 2000: { Ac: 21, Re: 22, Ss: 500 } },
    "4.0": { 2: { Ac: 0, Re: 1, Ss: 3 }, 3: { Ac: 0, Re: 1 }, 5: { Ac: 0, Re: 1, Ss: 3 }, 8: { Ac: 1, Re: 2, Ss: 13 }, 13: { Ac: 1, Re: 2 }, 20: { Ac: 2, Re: 3 }, 32: { Ac: 3, Re: 4 }, 50: { Ac: 5, Re: 6 }, 80: { Ac: 7, Re: 8 }, 125: { Ac: 10, Re: 11 }, 200: { Ac: 14, Re: 15 }, 315: { Ac: 21, Re: 22 }, 500: { Ac: 21, Re: 22, Ss: 315 }, 800: { Ac: 21, Re: 22, Ss: 315 }, 1250: { Ac: 21, Re: 22, Ss: 315 }, 2000: { Ac: 21, Re: 22, Ss: 315 } },
    "6.5": { 2: { Ac: 0, Re: 1 }, 3: { Ac: 0, Re: 1, Ss: 2 }, 5: { Ac: 1, Re: 2, Ss: 8 }, 8: { Ac: 1, Re: 2 }, 13: { Ac: 2, Re: 3 }, 20: { Ac: 3, Re: 4 }, 32: { Ac: 5, Re: 6 }, 50: { Ac: 7, Re: 8 }, 80: { Ac: 10, Re: 11 }, 125: { Ac: 14, Re: 15 }, 200: { Ac: 21, Re: 22 }, 315: { Ac: 21, Re: 22, Ss: 200 }, 500: { Ac: 21, Re: 22, Ss: 200 }, 800: { Ac: 21, Re: 22, Ss: 200 }, 1250: { Ac: 21, Re: 22, Ss: 200 }, 2000: { Ac: 21, Re: 22, Ss: 200 } }
};

function calculateAQL() {
    const quantity = parseInt(document.getElementById('quantity').value);
    const inspectionLevel = document.getElementById('inspectionLevel').value;
    const aqlLevel_Critical = document.getElementById('aqlLevel_Critical').value;
    const aqlLevel_Major = document.getElementById('aqlLevel_Major').value;
    const aqlLevel_Minor = document.getElementById('aqlLevel_Minor').value;

    const lotRange = LotRanges.find(r => quantity >= r.Min && (r.Max === Number.MAX_SAFE_INTEGER || quantity <= r.Max));
    if (!lotRange) return;

    let baseSample;
    switch (inspectionLevel) {
        case "I": baseSample = lotRange.SampleSizeI; break;
        case "II": baseSample = lotRange.SampleSizeII; break;
        case "III": baseSample = lotRange.SampleSizeIII; break;
        case "S1": baseSample = lotRange.SampleSizeS1; break;
        case "S2": baseSample = lotRange.SampleSizeS2; break;
        case "S3": baseSample = lotRange.SampleSizeS3; break;
        case "S4": baseSample = lotRange.SampleSizeS4; break;
        default: return;
    }
    if (baseSample === 0) return;

    baseSample = Math.min(baseSample, quantity);

    // const number = AqlNumbers[aqlLevel][baseSample];
    // if (!number) return;

    // const effectiveSample = number.Ss ? Math.min(number.Ss, quantity) : baseSample;

    // document.getElementById('sampleSize').value = `${effectiveSample} units`;
    // document.getElementById('acceptPoint').value = number.Ac;
    // document.getElementById('rejectPoint').value = number.Re;

    // Critical
    const number_Critical = AqlNumbers[aqlLevel_Critical][baseSample];
    if (!number_Critical) return;

    const effectiveSample_Critical = number_Critical.Ss ? Math.min(number_Critical.Ss, quantity) : baseSample;

    document.getElementById('sampleSize_Critical').value = `${effectiveSample_Critical} units`;
    document.getElementById('acceptPoint_Critical').value = number_Critical.Ac;
    document.getElementById('rejectPoint_Critical').value = number_Critical.Re;

    // Major

     const number_Major = AqlNumbers[aqlLevel_Major][baseSample];
    if (!number_Major) return;

    const effectiveSample_Major = number_Major.Ss ? Math.min(number_Major.Ss, quantity) : baseSample;

    document.getElementById('sampleSize_Major').value = `${effectiveSample_Major} units`;
    document.getElementById('acceptPoint_Major').value = number_Major.Ac;
    document.getElementById('rejectPoint_Major').value = number_Major.Re;

    // Minnor

     const number_Minor = AqlNumbers[aqlLevel_Minor][baseSample];
    if (!number_Minor) return;

    const effectiveSample_Minor = number_Minor.Ss ? Math.min(number_Minor.Ss, quantity) : baseSample;

    document.getElementById('sampleSize_Minor').value = `${effectiveSample_Minor} units`;
    document.getElementById('acceptPoint_Minor').value = number_Minor.Ac;
    document.getElementById('rejectPoint_Minor').value = number_Minor.Re;
}