import re
import argparse
import importlib
import json

# given a course from a sem_[0-9]+.py file, it will verify that the course is valid
# example course:
# {
#   'CRN': '64957',           'Course': 'CSE 107-01',   '*Campus': 'M', 'Days': ' M W F ',
#   'Time': '1300-1350',      'Location': 'CRAMER 239', 'Hrs': '4',     'Title': 'Intro to Programming Python',
#   'Instructor': 'Rita Kuo', 'Seats': '1',             'Limit': '32',  'Enroll': '31',
#   'Waitlist': '0',          'Course Fees': '$75',     'Type': 'class'
# }
def courseIsValid(course):
    # These columns were added since the codebase was first created.
    # The current workaround just to get things working again is
    # to call them all "optional".
    optionalcols = [u'Waitlist', u'Course Fees', u'Bookstore Link']

    # subclasses have a lot fewer requirements
    isSubclass = course[u'Type'] == u'subclass'
    if not isSubclass:
        # check that numbered columns are formatted like numbers
        numcols = [u'Enroll', u'Seats', u'Hrs', u'CRN', u'Limit', u'Waitlist']
        for col in numcols:
            if (col not in course) and (col in optionalcols):
                continue # ignore
            if not re.search('\-?[0-9]+', course[col]):
                print_verbose(col, 2)
                return False

        # check that there is a days string
        if (not u'Days' in course) and (u'Course' in course):
            course[u'Days'] = ' '
        if not re.search('[\ U][\ M][\ T][\ W][\ R][\ F][\ S]', course[u'Days']):
            if course[u'Days'] != ' ':
                print_verbose(u'Days', 2)
                return False

        # check that there is a course number
        if (not u'Course' in course) or (not re.search('[A-Z]+[\ ]+[0-9]+', course[u'Course'])):
            print_verbose(u'Course', 2)
            return False

    # check that there is a time
    if u'Time' in course:
        if not re.search('[0-9]+\-[0-9]+', course[u'Time']):
            print_verbose(u'Time', 2)
            return False
    else:
        course[u'Time'] = '';
    return True

def s(strToReplace):
    strToReplace = strToReplace.replace("&amp;", '&')
    strToReplace = strToReplace.replace("'", '')
    return strToReplace

# translates the given python file into something that php can understand
def translate_file(filename, path):
    m = importlib.import_module(filename)
    fout = open(path+filename+".php", "w")
    fout.write("<?php\n")
    fout.write("$semesterData = array(\n")
    
    fout.write("\t'name'=>'"+s(m.name)+"',\n")
    fout.write("\t'subjects'=>array(\n")
    for subject in m.subjects:
        fout.write("\t\t'"+s(subject[0])+"'=>'"+s(subject[1])+"',\n")
    fout.write("\t),\n")
    fout.write("\t'classes'=>array(\n")
    
    subject_index = 0
    for subject in m.classes:
        for course in subject:
            if not courseIsValid(course):
                print_verbose("invalid: "+str(course), 0)
                print_verbose('', 0)
                continue
            isSubclass = course[u'Type'] == u'subclass'
            fout.write("\t\tarray(\n");
            fout.write("\t\t\t'subject'=>'"+s(m.subjects[subject_index][0])+"'")
            for part in course:
                fout.write(",\n\t\t\t'"+s(part)+"'=>'"+s(course[part])+"'")
            fout.write("\n\t\t),\n")
        subject_index += 1
    
    fout.write("\t)\n")
    fout.write(");\n");
    fout.write("$a_retval = array('name'=>$semesterData['name'], 'subjects'=>$semesterData['subjects'], 'classes'=>$semesterData['classes']);\n")
    fout.write("$s_classes_json = json_encode($a_retval);\n")
    fout.write("?>\n")
    fout.close()

def main(parser):
    filenames = []
    path = ""

    banweb_terms = importlib.import_module('banweb_terms')
    for term in banweb_terms.terms:
        semester = term[0]
        filenames.append("sem_"+semester)

    if (hasattr(parser, 'path')) and (isinstance(parser.path, str)):
        path = parser.path
    
    fout = open(path+"banweb_terms.php", "w")
    fout.write("<?php\n")
    fout.write("$terms = array(")
    for term in banweb_terms.terms:
        # json dumps conveniently encodes strings for us
        # don't include surrounding brackets '[]'
        termstr = json.dumps(term)
        termstr = termstr[1:-1]
        fout.write(f"\n\t array({termstr}),")
    fout.write("\n);")
    fout.write("\n?>\n")
    
    for filename in filenames:
        translate_file(filename, path)

verbose = 0
def print_verbose(strval, verbosity):
    if verbose >= verbosity:
        print(strval)

if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument("--path", type=str, help="choose the location to save semester files to (must end in a slash, eg '/home/usr/stuff/')")
    parser.add_argument("-v", action="count", dest="verbose", help="Print out verbose text about what is happening")
    p = parser.parse_args()
    verbose = 0 if p.verbose is None else p.verbose
    main(p)
